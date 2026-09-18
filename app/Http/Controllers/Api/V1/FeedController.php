<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $followingIds = $request->user()->following()->pluck('following_id');

        $posts = Post::published()
            ->with(['author', 'category', 'tags'])
            ->whereIn('user_id', $followingIds)
            ->latest('published_at')
            ->paginate(min($request->get('per_page', 20), 100));

        return $this->success(PostResource::collection($posts));
    }
}
