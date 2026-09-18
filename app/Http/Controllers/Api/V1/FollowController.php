<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends ApiController
{
    public function store(User $user, Request $request): JsonResponse
    {
        if ($request->user()->id === $user->id) {
            return $this->error('You cannot follow yourself.', 409);
        }

        if ($request->user()->following()->where('following_id', $user->id)->exists()) {
            return $this->error('You are already following this user.', 409);
        }

        $request->user()->following()->create([
            'following_id' => $user->id,
        ]);

        return $this->created([
            'following' => true,
        ], 'User followed successfully.');
    }

    public function destroy(User $user, Request $request): JsonResponse
    {
        $request->user()->following()->where('following_id', $user->id)->delete();

        return $this->success([
            'following' => false,
        ], 'User unfollowed successfully.');
    }
}
