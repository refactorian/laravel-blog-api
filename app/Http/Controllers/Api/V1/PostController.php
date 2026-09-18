<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StorePostRequest;
use App\Http\Requests\Api\V1\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['author', 'category', 'tags'])
            ->published()
            ->latest('published_at');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        if ($request->has('author')) {
            $query->where('user_id', $request->author);
        }

        if ($request->has('sort')) {
            $sort = $request->sort;
            $direction = $sort[0] === '-' ? 'desc' : 'asc';
            $column = ltrim($sort, '-');
            $allowedSorts = ['created_at', 'updated_at', 'published_at', 'title'];
            if (in_array($column, $allowedSorts)) {
                $query->reorder($column, $direction);
            }
        }

        $perPage = min($request->get('per_page', 20), 100);
        $posts = $query->paginate($perPage);

        return $this->success(PostResource::collection($posts));
    }

    public function show(Post $post): JsonResponse
    {
        if ($post->status !== 'published') {
            try {
                $this->authorize('view', $post);
            } catch (AuthorizationException) {
                return $this->notFound('Post not found.');
            }
        }

        $post->load(['author', 'category', 'tags', 'comments.user', 'likes']);

        return $this->success(new PostResource($post));
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = Post::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'category_id' => $request->category_id,
            'featured_image' => $request->featured_image,
            'status' => $request->get('status', 'draft'),
        ]);

        if ($request->has('tag_ids')) {
            $post->tags()->sync($request->tag_ids);
        }

        if ($request->status === 'published') {
            $post->publish();
        }

        $post->load(['author', 'category', 'tags']);

        return $this->created(new PostResource($post), 'Post created successfully.');
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $post->update($request->validated());

        if ($request->has('tag_ids')) {
            $post->tags()->sync($request->tag_ids);
        }

        $post->load(['author', 'category', 'tags']);

        return $this->success(new PostResource($post->fresh()), 'Post updated successfully.');
    }

    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return $this->noContent();
    }

    public function publish(Post $post): JsonResponse
    {
        $this->authorize('publish', $post);

        $post->publish();

        return $this->success(new PostResource($post->fresh()->load(['author', 'category', 'tags'])), 'Post published successfully.');
    }

    public function unpublish(Post $post): JsonResponse
    {
        $this->authorize('unpublish', $post);

        $post->unpublish();

        return $this->success(new PostResource($post->fresh()->load(['author', 'category', 'tags'])), 'Post unpublished successfully.');
    }

    public function related(Post $post): JsonResponse
    {
        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->where(function ($query) use ($post) {
                $query->where('category_id', $post->category_id)
                    ->orWhereHas('tags', function ($q) use ($post) {
                        $q->whereIn('tags.id', $post->tags->pluck('id'));
                    });
            })
            ->with(['author', 'category', 'tags'])
            ->latest('published_at')
            ->limit(5)
            ->get();

        return $this->success(PostResource::collection($related));
    }

    public function markAsRead(Post $post, Request $request): JsonResponse
    {
        $request->user()->readingHistory()->updateOrCreate(
            ['post_id' => $post->id],
            ['read_at' => now()]
        );

        return $this->success(message: 'Post marked as read.');
    }
}
