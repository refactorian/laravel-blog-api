<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(?User $user, Post $post): bool
    {
        if ($post->status === 'published') {
            return true;
        }

        if ($user === null) {
            return false;
        }

        return $post->user_id === $user->id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAuthor();
    }

    public function update(User $user, Post $post): bool
    {
        return $post->user_id === $user->id || $user->isAdmin();
    }

    public function delete(User $user, Post $post): bool
    {
        return $post->user_id === $user->id || $user->isAdmin();
    }

    public function publish(User $user, Post $post): bool
    {
        return ($post->user_id === $user->id || $user->isAdmin()) && $post->status !== 'published';
    }

    public function unpublish(User $user, Post $post): bool
    {
        return ($post->user_id === $user->id || $user->isAdmin()) && $post->status === 'published';
    }
}
