<?php

namespace Tests\Feature\Api\V1;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_feed(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $author = User::factory()->author()->create();
        $user->following()->create(['following_id' => $author->id]);

        Post::factory()->published()->count(3)->create(['user_id' => $author->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/feed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_unauthenticated_user_cannot_get_feed(): void
    {
        $response = $this->getJson('/api/v1/feed');

        $response->assertStatus(401);
    }

    public function test_feed_returns_empty_when_not_following_anyone(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/feed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
            ]);
    }

    public function test_feed_only_shows_published_posts(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $author = User::factory()->author()->create();
        $user->following()->create(['following_id' => $author->id]);

        Post::factory()->published()->count(2)->create(['user_id' => $author->id]);
        Post::factory()->draft()->count(2)->create(['user_id' => $author->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/feed');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }
}
