<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BookmarkController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $bookmarks = $request->user()->bookmarks()
            ->with(['post.author', 'post.category', 'post.tags'])
            ->latest()
            ->paginate(min($request->input('per_page', 20), 100));

        $posts = $bookmarks->getCollection()->pluck('post')->filter();

        $paginated = new LengthAwarePaginator(
            $posts,
            $bookmarks->total(),
            $bookmarks->perPage(),
            $bookmarks->currentPage(),
            ['path' => $request->url()]
        );

        return $this->success(PostResource::collection($paginated));
    }

    public function store(Post $post, Request $request): JsonResponse
    {
        $request->user()->bookmarks()->create([
            'post_id' => $post->id,
        ]);

        return $this->created([
            'bookmarked' => true,
        ], 'Post bookmarked successfully.');
    }

    public function destroy(Post $post, Request $request): JsonResponse
    {
        $request->user()->bookmarks()->where('post_id', $post->id)->delete();

        return $this->success([
            'bookmarked' => false,
        ], 'Bookmark removed successfully.');
    }

    public function status(Post $post, Request $request): JsonResponse
    {
        $isBookmarked = $request->user()->bookmarks()->where('post_id', $post->id)->exists();

        return $this->success([
            'bookmarked' => $isBookmarked,
        ]);
    }
}
