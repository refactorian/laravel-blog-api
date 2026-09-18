<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreCategoryRequest;
use App\Http\Requests\Api\V1\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CategoryController extends ApiController
{
    public function index()
    {
        $categories = Cache::remember('categories', 3600, function () {
            return Category::withCount('posts')->orderBy('name')->get();
        });

        return $this->success(CategoryResource::collection($categories));
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadCount('posts');

        return $this->success(new CategoryResource($category));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        Cache::forget('categories');

        return $this->created(new CategoryResource($category), 'Category created successfully.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        Cache::forget('categories');

        return $this->success(new CategoryResource($category->fresh()), 'Category updated successfully.');
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        Cache::forget('categories');

        return $this->noContent();
    }
}
