<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    public function create(User $user): bool
    {
        return $user->isAuthor();
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->isAuthor();
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->isAdmin();
    }
}
