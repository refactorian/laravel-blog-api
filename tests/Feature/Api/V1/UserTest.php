<?php

namespace Tests\Feature\Api\V1;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_public_profile(): void
    {
        $user = User::factory()->author()->create();

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
            ])
            ->assertJsonStructure([
                'data' => ['id', 'name', 'bio', 'avatar', 'role', 'posts_count', 'followers_count', 'following_count', 'created_at'],
            ]);
    }

    public function test_user_profile_does_not_expose_email(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertArrayNotHasKey('email', $response->json('data'));
    }

    public function test_user_can_get_user_posts(): void
    {
        $user = User::factory()->author()->create();
        Post::factory()->published()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/users/{$user->id}/posts");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_user_posts_only_returns_published(): void
    {
        $user = User::factory()->author()->create();
        Post::factory()->published()->count(2)->create(['user_id' => $user->id]);
        Post::factory()->draft()->count(2)->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/users/{$user->id}/posts");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_user_not_found_returns_404(): void
    {
        $response = $this->getJson('/api/v1/users/99999');

        $response->assertStatus(404);
    }
}
