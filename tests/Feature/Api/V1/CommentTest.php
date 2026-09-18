<?php

namespace Tests\Feature\Api\V1;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_comments(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();
        Comment::factory()->count(5)->create(['post_id' => $post->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/v1/posts/{$post->id}/comments");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_authenticated_user_can_create_comment(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/comments", [
                'body' => 'This is a comment.',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Comment created successfully.',
            ]);

        $this->assertDatabaseHas('comments', ['body' => 'This is a comment.']);
    }

    public function test_unauthenticated_user_cannot_create_comment(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->postJson("/api/v1/posts/{$post->id}/comments", [
            'body' => 'This is a comment.',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_create_reply(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();
        $parentComment = Comment::factory()->create(['post_id' => $post->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/comments", [
                'body' => 'This is a reply.',
                'parent_id' => $parentComment->id,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Comment created successfully.',
            ]);

        $this->assertDatabaseHas('comments', [
            'body' => 'This is a reply.',
            'parent_id' => $parentComment->id,
        ]);
    }

    public function test_user_can_update_own_comment(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/v1/comments/{$comment->id}", [
                'body' => 'Updated comment.',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Comment updated successfully.',
            ]);
    }

    public function test_user_cannot_update_other_users_comment(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $comment = Comment::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/v1/comments/{$comment->id}", [
                'body' => 'Updated comment.',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_other_users_comment(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $comment = Comment::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_update_any_comment(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('auth-token')->plainTextToken;
        $comment = Comment::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/v1/comments/{$comment->id}", [
                'body' => 'Admin updated comment.',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Comment updated successfully.',
            ]);
    }

    public function test_admin_can_delete_any_comment(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('auth-token')->plainTextToken;
        $comment = Comment::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_unauthenticated_user_cannot_update_comment(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->putJson("/api/v1/comments/{$comment->id}", [
            'body' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_comment(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(401);
    }

    public function test_comment_requires_body(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/comments", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    }

    public function test_reply_must_belong_to_same_post(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $post = Post::factory()->published()->create();
        $otherPost = Post::factory()->published()->create();
        $parentComment = Comment::factory()->create(['post_id' => $otherPost->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/posts/{$post->id}/comments", [
                'body' => 'Reply to wrong post.',
                'parent_id' => $parentComment->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }
}
