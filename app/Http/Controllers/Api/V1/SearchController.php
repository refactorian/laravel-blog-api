<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2'],
            'type' => ['sometimes', 'string', 'in:posts,users'],
        ]);

        $query = $request->q;
        $type = $request->get('type', 'posts');

        if ($type === 'posts') {
            $posts = Post::published()
                ->with(['author', 'category', 'tags'])
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                })
                ->latest('published_at')
                ->paginate(min($request->get('per_page', 20), 100));

            return $this->success(PostResource::collection($posts));
        }

        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('bio', 'like', "%{$query}%")
            ->limit(20)
            ->get();

        return $this->success(UserResource::collection($users));
    }
}
