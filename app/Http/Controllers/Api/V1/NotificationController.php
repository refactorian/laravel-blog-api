<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()
            ->latest()
            ->paginate(min($request->get('per_page', 20), 100));

        return $this->success(NotificationResource::collection($notifications));
    }

    public function markAsRead($notification, Request $request): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($notification);
        $notification->markAsRead();

        return $this->success(message: 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return $this->success(message: 'All notifications marked as read.');
    }
}
