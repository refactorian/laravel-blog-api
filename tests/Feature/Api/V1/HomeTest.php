<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_home_data(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $post = Post::factory()->published()->create(['category_id' => $category->id]);
        $post->tags()->attach($tag);

        $response = $this->getJson('/api/v1/home');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'featured_posts',
                    'latest_posts',
                    'popular_posts',
                    'categories',
                    'trending_tags',
                ],
            ]);
    }

    public function test_home_returns_empty_data_when_no_content(): void
    {
        $response = $this->getJson('/api/v1/home');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'featured_posts' => [],
                    'latest_posts' => [],
                    'popular_posts' => [],
                    'categories' => [],
                    'trending_tags' => [],
                ],
            ]);
    }

    public function test_home_is_publicly_accessible(): void
    {
        $response = $this->getJson('/api/v1/home');

        $response->assertStatus(200);
    }
}
