<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreTagRequest;
use App\Http\Requests\Api\V1\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class TagController extends ApiController
{
    public function index()
    {
        $tags = Cache::remember('tags', 3600, function () {
            return Tag::withCount('posts')->orderBy('name')->get();
        });

        return $this->success(TagResource::collection($tags));
    }

    public function show(Tag $tag): JsonResponse
    {
        $tag->loadCount('posts');

        return $this->success(new TagResource($tag));
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $tag = Tag::create($request->validated());

        Cache::forget('tags');

        return $this->created(new TagResource($tag), 'Tag created successfully.');
    }

    public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
    {
        $tag->update($request->validated());

        Cache::forget('tags');

        return $this->success(new TagResource($tag->fresh()), 'Tag updated successfully.');
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        Cache::forget('tags');

        return $this->noContent();
    }
}
