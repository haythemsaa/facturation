<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Get user notifications.
     */
    public function index(Request $request)
    {
        $query = Notification::forUser($request->user()->id)
            ->orderByDesc('created_at');

        // Filter by read status
        if ($request->has('unread_only')) {
            $query->unread();
        }

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Pagination
        $notifications = $query->paginate($request->get('per_page', 15));

        return NotificationResource::collection($notifications);
    }

    /**
     * Get unread count.
     */
    public function unreadCount(Request $request)
    {
        $count = $this->notificationService->getUnreadCount($request->user());

        return response()->json([
            'unread_count' => $count,
        ]);
    }

    /**
     * Get a single notification.
     */
    public function show(string $id)
    {
        $notification = Notification::forUser(auth()->id())
            ->findOrFail($id);

        return new NotificationResource($notification);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(string $id)
    {
        $notification = Notification::forUser(auth()->id())
            ->findOrFail($id);

        $notification->markAsRead();

        return new NotificationResource($notification);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(string $id)
    {
        $notification = Notification::forUser(auth()->id())
            ->findOrFail($id);

        $notification->markAsUnread();

        return new NotificationResource($notification);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $count = $this->notificationService->markAllAsRead($request->user());

        return response()->json([
            'message' => "Marked {$count} notifications as read",
            'count' => $count,
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $id)
    {
        $notification = Notification::forUser(auth()->id())
            ->findOrFail($id);

        $notification->delete();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete all read notifications.
     */
    public function deleteAllRead(Request $request)
    {
        $count = Notification::forUser($request->user()->id)
            ->read()
            ->delete();

        return response()->json([
            'message' => "Deleted {$count} read notifications",
            'count' => $count,
        ]);
    }
}
