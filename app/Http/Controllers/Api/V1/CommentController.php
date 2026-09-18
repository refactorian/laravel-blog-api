<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreCommentRequest;
use App\Http\Requests\Api\V1\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends ApiController
{
    public function index(Post $post, Request $request): JsonResponse
    {
        $comments = Comment::with(['user', 'replies.user'])
            ->where('post_id', $post->id)
            ->whereNull('parent_id')
            ->withCount('replies')
            ->latest()
            ->paginate(min($request->input('per_page', 20), 100));

        return $this->success(CommentResource::collection($comments));
    }

    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'parent_id' => $request->parent_id,
            'body' => $request->body,
        ]);

        $comment->load('user');

        return $this->created(new CommentResource($comment), 'Comment created successfully.');
    }

    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $comment->update($request->validated());

        $comment->load('user');

        return $this->success(new CommentResource($comment->fresh()), 'Comment updated successfully.');
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return $this->noContent();
    }
}
