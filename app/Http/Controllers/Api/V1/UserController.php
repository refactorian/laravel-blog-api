<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    public function show(User $user): JsonResponse
    {
        $user->loadCount(['posts' => function ($query) {
            $query->published();
        }, 'followers', 'following']);

        return $this->success(new UserResource($user));
    }

    public function posts(User $user, Request $request): JsonResponse
    {
        $posts = $user->posts()
            ->published()
            ->with(['category', 'tags'])
            ->latest('published_at')
            ->paginate(min($request->get('per_page', 20), 100));

        return $this->success(PostResource::collection($posts));
    }

    public function followers(User $user, Request $request)
    {
        $followerIds = Follow::where('following_id', $user->id)->pluck('follower_id');
        $followers = User::whereIn('id', $followerIds)
            ->paginate(min($request->get('per_page', 20), 100));

        return $this->success(UserResource::collection($followers));
    }

    public function following(User $user, Request $request)
    {
        $followingIds = Follow::where('follower_id', $user->id)->pluck('following_id');
        $following = User::whereIn('id', $followingIds)
            ->paginate(min($request->get('per_page', 20), 100));

        return $this->success(UserResource::collection($following));
    }
}
