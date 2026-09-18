<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function createNotificationFor(User $user, ?string $readAt = null): string
    {
        $id = fake()->uuid();
        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'App\\Notifications\\PostLikedNotification',
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
            'data' => json_encode(['message' => 'test']),
            'read_at' => $readAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    public function test_user_can_list_notifications(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $this->createNotificationFor($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_unauthenticated_user_cannot_list_notifications(): void
    {
        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(401);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $notificationId = $this->createNotificationFor($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/notifications/{$notificationId}/read");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification marked as read.',
            ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationId,
        ]);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $this->createNotificationFor($user);
        $this->createNotificationFor($user);
        $this->createNotificationFor($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/notifications/read-all');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'All notifications marked as read.',
            ]);
    }

    public function test_unauthenticated_user_cannot_mark_notifications(): void
    {
        $response = $this->postJson('/api/v1/notifications/some-id/read');

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_mark_all_as_read(): void
    {
        $response = $this->postJson('/api/v1/notifications/read-all');

        $response->assertStatus(401);
    }

    public function test_user_cannot_mark_other_users_notification(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $otherUser = User::factory()->create();
        $otherNotificationId = $this->createNotificationFor($otherUser);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/notifications/{$otherNotificationId}/read");

        $response->assertStatus(404);
    }
}
