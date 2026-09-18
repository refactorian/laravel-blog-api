<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_categories(): void
    {
        Category::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug'],
                ],
            ]);
    }

    public function test_user_can_get_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/v1/categories/{$category->slug}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
            ]);
    }

    public function test_author_can_create_category(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/categories', [
                'name' => 'Technology',
                'description' => 'Technology related posts',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Category created successfully.',
            ]);

        $this->assertDatabaseHas('categories', ['name' => 'Technology']);
    }

    public function test_regular_user_cannot_create_category(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/categories', [
                'name' => 'Technology',
            ]);

        $response->assertStatus(403);
    }

    public function test_author_can_update_category(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/v1/categories/{$category->id}", [
                'name' => 'Updated Category',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Category updated successfully.',
            ]);
    }

    public function test_admin_can_delete_category(): void
    {
        $user = User::factory()->admin()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_not_found_returns_404(): void
    {
        $response = $this->getJson('/api/v1/categories/non-existent-slug');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_create_category(): void
    {
        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Technology',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_update_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(401);
    }

    public function test_regular_user_cannot_delete_category(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(403);
    }

    public function test_author_cannot_delete_category(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(403);
    }

    public function test_category_requires_name(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_category_name_must_be_unique(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        Category::factory()->create(['name' => 'Technology']);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/categories', [
                'name' => 'Technology',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
