<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookmarkController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\FeedController;
use App\Http\Controllers\Api\V1\FollowController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\LikeController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ReadingHistoryController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    // Profile routes
    Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/password', [ProfileController::class, 'changePassword']);
    });

    // Public routes
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{post:slug}', [PostController::class, 'show']);
    Route::get('/posts/{post}/related', [PostController::class, 'related']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show']);
    Route::get('/tags', [TagController::class, 'index']);
    Route::get('/tags/{tag:slug}', [TagController::class, 'show']);
    Route::get('/search', [SearchController::class, 'index']);
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::get('/users/{user}/posts', [UserController::class, 'posts']);
    Route::get('/users/{user}/followers', [UserController::class, 'followers']);
    Route::get('/users/{user}/following', [UserController::class, 'following']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        // Posts
        Route::post('/posts', [PostController::class, 'store']);
        Route::put('/posts/{post}', [PostController::class, 'update']);
        Route::delete('/posts/{post}', [PostController::class, 'destroy']);
        Route::post('/posts/{post}/publish', [PostController::class, 'publish']);
        Route::post('/posts/{post}/unpublish', [PostController::class, 'unpublish']);
        Route::post('/posts/{post}/read', [PostController::class, 'markAsRead']);

        // Comments
        Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
        Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
        Route::put('/comments/{comment}', [CommentController::class, 'update']);
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

        // Likes
        Route::post('/posts/{post}/like', [LikeController::class, 'store']);
        Route::delete('/posts/{post}/like', [LikeController::class, 'destroy']);
        Route::get('/posts/{post}/like', [LikeController::class, 'status']);

        // Bookmarks
        Route::get('/bookmarks', [BookmarkController::class, 'index']);
        Route::post('/posts/{post}/bookmark', [BookmarkController::class, 'store']);
        Route::delete('/posts/{post}/bookmark', [BookmarkController::class, 'destroy']);
        Route::get('/posts/{post}/bookmark', [BookmarkController::class, 'status']);

        // Follow
        Route::post('/users/{user}/follow', [FollowController::class, 'store']);
        Route::delete('/users/{user}/follow', [FollowController::class, 'destroy']);

        // Categories (admin/author only)
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // Tags (admin/author only)
        Route::post('/tags', [TagController::class, 'store']);
        Route::put('/tags/{tag}', [TagController::class, 'update']);
        Route::delete('/tags/{tag}', [TagController::class, 'destroy']);

        // Media
        Route::post('/media', [MediaController::class, 'store']);
        Route::delete('/media/{media}', [MediaController::class, 'destroy']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

        // Feed
        Route::get('/feed', [FeedController::class, 'index']);

        // Reading History
        Route::get('/reading-history', [ReadingHistoryController::class, 'index']);
        Route::delete('/reading-history', [ReadingHistoryController::class, 'destroy']);
    });
});
