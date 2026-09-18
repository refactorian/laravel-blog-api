<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends ApiController
{
    public function store(Post $post, Request $request): JsonResponse
    {
        if ($request->user()->likes()->where('post_id', $post->id)->exists()) {
            return $this->error('You have already liked this post.', 409);
        }

        $like = $request->user()->likes()->create([
            'post_id' => $post->id,
        ]);

        return $this->created([
            'liked' => true,
            'likes_count' => $post->likes()->count(),
        ], 'Post liked successfully.');
    }

    public function destroy(Post $post, Request $request): JsonResponse
    {
        $request->user()->likes()->where('post_id', $post->id)->delete();

        return $this->success([
            'liked' => false,
            'likes_count' => $post->likes()->count(),
        ], 'Post unliked successfully.');
    }

    public function status(Post $post, Request $request): JsonResponse
    {
        $isLiked = $request->user()->likes()->where('post_id', $post->id)->exists();

        return $this->success([
            'liked' => $isLiked,
            'likes_count' => $post->likes()->count(),
        ]);
    }
}
