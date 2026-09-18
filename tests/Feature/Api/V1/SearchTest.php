<?php

namespace Tests\Feature\Api\V1;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_search_posts(): void
    {
        Post::factory()->published()->create(['title' => 'Laravel Tutorial']);
        Post::factory()->published()->create(['title' => 'Flutter Guide']);

        $response = $this->getJson('/api/v1/search?q=laravel');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_search_requires_query(): void
    {
        $response = $this->getJson('/api/v1/search');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }

    public function test_user_can_search_users(): void
    {
        User::factory()->create(['name' => 'John Doe']);

        $response = $this->getJson('/api/v1/search?q=john&type=users');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
