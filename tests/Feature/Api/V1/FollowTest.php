<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_follow_another_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $author = User::factory()->author()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/users/{$author->id}/follow");

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'following' => true,
                ],
            ]);

        $this->assertDatabaseHas('follows', [
            'follower_id' => $user->id,
            'following_id' => $author->id,
        ]);
    }

    public function test_user_can_unfollow_another_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $author = User::factory()->author()->create();
        $user->following()->create(['following_id' => $author->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/users/{$author->id}/follow");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'following' => false,
                ],
            ]);

        $this->assertDatabaseMissing('follows', [
            'follower_id' => $user->id,
            'following_id' => $author->id,
        ]);
    }

    public function test_user_cannot_follow_themselves(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/users/{$user->id}/follow");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'You cannot follow yourself.',
            ]);
    }

    public function test_user_can_list_followers(): void
    {
        $user = User::factory()->create();
        $follower = User::factory()->create();
        $follower->following()->create(['following_id' => $user->id]);

        $response = $this->getJson("/api/v1/users/{$user->id}/followers");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_user_can_list_following(): void
    {
        $user = User::factory()->create();
        $following = User::factory()->create();
        $user->following()->create(['following_id' => $following->id]);

        $response = $this->getJson("/api/v1/users/{$user->id}/following");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_unauthenticated_user_cannot_follow(): void
    {
        $author = User::factory()->author()->create();

        $response = $this->postJson("/api/v1/users/{$author->id}/follow");

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_unfollow(): void
    {
        $author = User::factory()->author()->create();

        $response = $this->deleteJson("/api/v1/users/{$author->id}/follow");

        $response->assertStatus(401);
    }

    public function test_follow_nonexistent_user_returns_404(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/users/99999/follow');

        $response->assertStatus(404);
    }

    public function test_duplicate_follow_returns_error(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $author = User::factory()->author()->create();
        $user->following()->create(['following_id' => $author->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/users/{$author->id}/follow");

        $response->assertStatus(409);
    }
}
