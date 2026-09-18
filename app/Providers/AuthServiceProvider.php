<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Policies\CategoryPolicy;
use App\Policies\CommentPolicy;
use App\Policies\MediaPolicy;
use App\Policies\PostPolicy;
use App\Policies\TagPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Post::class => PostPolicy::class,
        Comment::class => CommentPolicy::class,
        Category::class => CategoryPolicy::class,
        Tag::class => TagPolicy::class,
        Media::class => MediaPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
