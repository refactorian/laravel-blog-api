<?php

namespace Tests\Feature\Api\V1;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_like_post(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/like");

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'liked' => true,
                ],
            ]);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_user_can_unlike_post(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();
        $user->likes()->create(['post_id' => $post->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/posts/{$post->id}/like");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'liked' => false,
                ],
            ]);

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_user_can_check_like_status(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/v1/posts/{$post->id}/like");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'liked' => false,
                ],
            ]);
    }

    public function test_user_cannot_like_post_twice(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();
        $user->likes()->create(['post_id' => $post->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/like");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'You have already liked this post.',
            ]);
    }

    public function test_unauthenticated_user_cannot_like_post(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->postJson("/api/v1/posts/{$post->id}/like");

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_unlike_post(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->deleteJson("/api/v1/posts/{$post->id}/like");

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_check_like_status(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->getJson("/api/v1/posts/{$post->id}/like");

        $response->assertStatus(401);
    }

    public function test_like_status_returns_true_when_liked(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();
        $user->likes()->create(['post_id' => $post->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/v1/posts/{$post->id}/like");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'liked' => true,
                    'likes_count' => 1,
                ],
            ]);
    }
}
