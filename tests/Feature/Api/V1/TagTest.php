<?php

namespace Tests\Feature\Api\V1;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_tags(): void
    {
        Tag::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/tags');

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

    public function test_user_can_get_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->getJson("/api/v1/tags/{$tag->slug}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ],
            ]);
    }

    public function test_author_can_create_tag(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/tags', [
                'name' => 'Laravel',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Tag created successfully.',
            ]);

        $this->assertDatabaseHas('tags', ['name' => 'Laravel']);
    }

    public function test_regular_user_cannot_create_tag(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/tags', [
                'name' => 'Laravel',
            ]);

        $response->assertStatus(403);
    }

    public function test_author_can_update_tag(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $tag = Tag::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/v1/tags/{$tag->id}", [
                'name' => 'Updated Tag',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Tag updated successfully.',
            ]);
    }

    public function test_admin_can_delete_tag(): void
    {
        $user = User::factory()->admin()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $tag = Tag::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/tags/{$tag->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_tag_not_found_returns_404(): void
    {
        $response = $this->getJson('/api/v1/tags/non-existent-slug');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_create_tag(): void
    {
        $response = $this->postJson('/api/v1/tags', [
            'name' => 'Laravel',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_update_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->putJson("/api/v1/tags/{$tag->id}", [
            'name' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->deleteJson("/api/v1/tags/{$tag->id}");

        $response->assertStatus(401);
    }

    public function test_regular_user_cannot_delete_tag(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $tag = Tag::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/tags/{$tag->id}");

        $response->assertStatus(403);
    }

    public function test_author_cannot_delete_tag(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $tag = Tag::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/tags/{$tag->id}");

        $response->assertStatus(403);
    }

    public function test_tag_requires_name(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/tags', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_tag_name_must_be_unique(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        Tag::factory()->create(['name' => 'Laravel']);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/tags', [
                'name' => 'Laravel',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
