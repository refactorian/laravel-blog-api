<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\TagResource;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HomeController extends ApiController
{
    public function index(): JsonResponse
    {
        $data = Cache::remember('home', 3600, function () {
            return [
                'featured_posts' => Post::published()
                    ->with(['author', 'category', 'tags'])
                    ->latest('published_at')
                    ->limit(5)
                    ->get(),
                'latest_posts' => Post::published()
                    ->with(['author', 'category', 'tags'])
                    ->latest('published_at')
                    ->limit(10)
                    ->get(),
                'popular_posts' => Post::published()
                    ->with(['author', 'category', 'tags'])
                    ->withCount('likes')
                    ->orderByDesc('likes_count')
                    ->limit(10)
                    ->get(),
                'categories' => Category::withCount('posts')
                    ->orderByDesc('posts_count')
                    ->limit(10)
                    ->get(),
                'trending_tags' => Tag::withCount('posts')
                    ->orderByDesc('posts_count')
                    ->limit(10)
                    ->get(),
            ];
        });

        return $this->success([
            'featured_posts' => PostResource::collection($data['featured_posts']),
            'latest_posts' => PostResource::collection($data['latest_posts']),
            'popular_posts' => PostResource::collection($data['popular_posts']),
            'categories' => CategoryResource::collection($data['categories']),
            'trending_tags' => TagResource::collection($data['trending_tags']),
        ]);
    }
}
