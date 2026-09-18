<?php

namespace Tests\Feature\Api\V1;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_bookmark_post(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/bookmark");

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'bookmarked' => true,
                ],
            ]);

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_user_can_remove_bookmark(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();
        $user->bookmarks()->create(['post_id' => $post->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/posts/{$post->id}/bookmark");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'bookmarked' => false,
                ],
            ]);

        $this->assertDatabaseMissing('bookmarks', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_user_can_list_bookmarks(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();
        $user->bookmarks()->create(['post_id' => $post->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/bookmarks');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_user_can_check_bookmark_status(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/v1/posts/{$post->id}/bookmark");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'bookmarked' => false,
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_bookmark_post(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->postJson("/api/v1/posts/{$post->id}/bookmark");

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_remove_bookmark(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->deleteJson("/api/v1/posts/{$post->id}/bookmark");

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_list_bookmarks(): void
    {
        $response = $this->getJson('/api/v1/bookmarks');

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_check_bookmark_status(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->getJson("/api/v1/posts/{$post->id}/bookmark");

        $response->assertStatus(401);
    }

    public function test_bookmark_status_returns_true_when_bookmarked(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();
        $user->bookmarks()->create(['post_id' => $post->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/v1/posts/{$post->id}/bookmark");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'bookmarked' => true,
                ],
            ]);
    }
}
