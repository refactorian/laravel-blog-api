<?php

namespace Tests\Feature\Api\V1;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_reading_history(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $post = Post::factory()->published()->create();
        $user->readingHistory()->create(['post_id' => $post->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/reading-history');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_unauthenticated_user_cannot_get_reading_history(): void
    {
        $response = $this->getJson('/api/v1/reading-history');

        $response->assertStatus(401);
    }

    public function test_user_can_clear_reading_history(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $post = Post::factory()->published()->create();
        $user->readingHistory()->create(['post_id' => $post->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/reading-history');

        $response->assertStatus(204);
        $this->assertDatabaseMissing('reading_history', ['user_id' => $user->id]);
    }

    public function test_unauthenticated_user_cannot_clear_reading_history(): void
    {
        $response = $this->deleteJson('/api/v1/reading-history');

        $response->assertStatus(401);
    }

    public function test_user_can_mark_post_as_read(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post marked as read.',
            ]);

        $this->assertDatabaseHas('reading_history', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_marking_same_post_as_read_is_idempotent(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/read");

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/read");

        $response->assertStatus(200);

        $this->assertDatabaseCount('reading_history', 1);
    }

    public function test_unauthenticated_user_cannot_mark_as_read(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->postJson("/api/v1/posts/{$post->id}/read");

        $response->assertStatus(401);
    }
}
