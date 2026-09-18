<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_posts(): void
    {
        Post::factory()->published()->count(5)->create();

        $response = $this->getJson('/api/v1/posts');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_user_can_get_post_by_slug(): void
    {
        $post = Post::factory()->published()->create([
            'slug' => 'test-post',
        ]);

        $response = $this->getJson('/api/v1/posts/test-post');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => 'test-post',
                ],
            ]);
    }

    public function test_author_can_create_post(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/posts', [
                'title' => 'Test Post',
                'content' => 'This is a test post content.',
                'category_id' => $category->id,
                'tag_ids' => $tags->pluck('id')->toArray(),
                'status' => 'draft',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Post created successfully.',
            ]);

        $this->assertDatabaseHas('posts', ['title' => 'Test Post']);
    }

    public function test_regular_user_cannot_create_post(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/posts', [
                'title' => 'Test Post',
                'content' => 'This is a test post content.',
            ]);

        $response->assertStatus(403);
    }

    public function test_author_can_update_post(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/v1/posts/{$post->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post updated successfully.',
            ]);
    }

    public function test_author_cannot_update_other_users_post(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/v1/posts/{$post->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(403);
    }

    public function test_author_can_delete_post(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_author_can_publish_post(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->draft()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/publish");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post published successfully.',
            ]);

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'status' => 'published']);
    }

    public function test_user_can_search_posts(): void
    {
        Post::factory()->published()->create(['title' => 'Laravel Tutorial']);
        Post::factory()->published()->create(['title' => 'Flutter Guide']);

        $response = $this->getJson('/api/v1/posts?search=laravel');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_user_can_filter_posts_by_category(): void
    {
        $category = Category::factory()->create();
        Post::factory()->published()->count(3)->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/v1/posts?category={$category->slug}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_user_can_filter_posts_by_tag(): void
    {
        $tag = Tag::factory()->create();
        $post = Post::factory()->published()->create();
        $post->tags()->attach($tag);

        $response = $this->getJson("/api/v1/posts?tag={$tag->slug}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_post_not_found_returns_404(): void
    {
        $response = $this->getJson('/api/v1/posts/non-existent-slug');

        $response->assertStatus(404);
    }

    public function test_unpublished_post_not_visible_to_public(): void
    {
        Post::factory()->draft()->create(['slug' => 'draft-post']);

        $response = $this->getJson('/api/v1/posts/draft-post');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_create_post(): void
    {
        $response = $this->postJson('/api/v1/posts', [
            'title' => 'Test',
            'content' => 'Content',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_update_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->putJson("/api/v1/posts/{$post->id}", [
            'title' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_publish_post(): void
    {
        $post = Post::factory()->draft()->create();

        $response = $this->postJson("/api/v1/posts/{$post->id}/publish");

        $response->assertStatus(401);
    }

    public function test_author_cannot_delete_other_users_post(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_any_post(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_admin_can_update_any_post(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/v1/posts/{$post->id}", [
                'title' => 'Admin Updated Title',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post updated successfully.',
            ]);
    }

    public function test_author_cannot_publish_other_users_post(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->draft()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/publish");

        $response->assertStatus(403);
    }

    public function test_admin_can_publish_any_draft(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->draft()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/publish");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post published successfully.',
            ]);
    }

    public function test_author_can_unpublish_own_post(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/unpublish");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post unpublished successfully.',
            ]);

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'status' => 'draft']);
    }

    public function test_author_cannot_unpublish_other_users_post(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/unpublish");

        $response->assertStatus(403);
    }

    public function test_admin_can_unpublish_any_post(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/unpublish");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post unpublished successfully.',
            ]);
    }

    public function test_user_can_get_related_posts(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->published()->create(['category_id' => $category->id]);
        Post::factory()->published()->count(3)->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/v1/posts/{$post->id}/related");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_related_posts_excludes_current_post(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->published()->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/v1/posts/{$post->id}/related");

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $relatedPost) {
            $this->assertNotEquals($post->id, $relatedPost['id']);
        }
    }

    public function test_user_can_filter_posts_by_author(): void
    {
        $author = User::factory()->author()->create();
        Post::factory()->published()->count(3)->create(['user_id' => $author->id]);

        $response = $this->getJson("/api/v1/posts?author={$author->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_user_can_sort_posts(): void
    {
        Post::factory()->published()->count(3)->create();

        $response = $this->getJson('/api/v1/posts?sort=-created_at');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_post_requires_title_and_content(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/posts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content']);
    }

    public function test_post_validates_category_exists(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/posts', [
                'title' => 'Test',
                'content' => 'Content',
                'category_id' => 99999,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_post_validates_tag_ids_exist(): void
    {
        $user = User::factory()->author()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/posts', [
                'title' => 'Test',
                'content' => 'Content',
                'tag_ids' => [99999],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tag_ids.0']);
    }
}
