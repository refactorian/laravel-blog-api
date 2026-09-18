<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when($this->isCurrentUser($request), fn () => $this->email),
            'bio' => $this->bio,
            'avatar' => $this->avatar,
            'role' => $this->role,
            'posts_count' => $this->whenCounted('posts'),
            'followers_count' => $this->followers_count,
            'following_count' => $this->following_count,
            'created_at' => $this->created_at->toISOString(),
        ];
    }

    protected function isCurrentUser(Request $request): bool
    {
        return $request->user() && $request->user()->id === $this->id;
    }
}
