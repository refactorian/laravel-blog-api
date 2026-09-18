<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\PostResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ReadingHistoryController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $history = $request->user()->readingHistory()
            ->with(['post.author', 'post.category', 'post.tags'])
            ->latest('read_at')
            ->paginate(min($request->input('per_page', 20), 100));

        $posts = $history->getCollection()->pluck('post')->filter();

        $paginated = new LengthAwarePaginator(
            $posts,
            $history->total(),
            $history->perPage(),
            $history->currentPage(),
            ['path' => $request->url()]
        );

        return $this->success(PostResource::collection($paginated));
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->readingHistory()->delete();

        return $this->noContent();
    }
}
