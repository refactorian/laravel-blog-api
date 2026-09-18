<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->when($request->routeIs('posts.show'), 'content'),
            'featured_image' => $this->featured_image,
            'status' => $this->status,
            'published_at' => $this->published_at?->toISOString(),
            'reading_time' => $this->reading_time,
            'likes_count' => $this->whenCounted('likes'),
            'comments_count' => $this->whenCounted('comments'),
            'author' => new UserResource($this->whenLoaded('author')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'is_liked' => $this->when($request->user(), function () use ($request) {
                return $this->likes->contains('user_id', $request->user()->id);
            }),
            'is_bookmarked' => $this->when($request->user(), function () use ($request) {
                return $this->bookmarks->contains('user_id', $request->user()->id);
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
