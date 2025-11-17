<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Create a notification for a user.
     */
    public function create(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $icon = null,
        string $priority = 'normal',
        array $data = []
    ): Notification {
        return Notification::create([
            'id' => Str::uuid(),
            'tenant_id' => $user->tenant_id,
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'icon' => $icon ?? $this->getDefaultIcon($type),
            'priority' => $priority,
            'data' => $data,
        ]);
    }

    /**
     * Send notification to multiple users.
     */
    public function sendToUsers(
        array $users,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        string $priority = 'normal',
        array $data = []
    ): int {
        $count = 0;
        foreach ($users as $user) {
            $this->create($user, $type, $title, $message, $actionUrl, null, $priority, $data);
            $count++;
        }
        return $count;
    }

    /**
     * Send notification to all tenant users with specific role.
     */
    public function sendToRole(
        int $tenantId,
        string $role,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        string $priority = 'normal'
    ): int {
        $users = User::where('tenant_id', $tenantId)
            ->whereHas('roles', function ($query) use ($role) {
                $query->where('name', $role);
            })
            ->get();

        return $this->sendToUsers($users, $type, $title, $message, $actionUrl, $priority);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(string $notificationId): bool
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        return false;
    }

    /**
     * Mark all user notifications as read.
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::forUser($user->id)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread count for user.
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::forUser($user->id)
            ->unread()
            ->count();
    }

    /**
     * Get recent notifications for user.
     */
    public function getRecent(User $user, int $limit = 10)
    {
        return Notification::forUser($user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Delete old read notifications.
     */
    public function cleanupOldNotifications(int $daysOld = 30): int
    {
        return Notification::read()
            ->where('created_at', '<', now()->subDays($daysOld))
            ->delete();
    }

    /**
     * Get default icon based on notification type.
     */
    private function getDefaultIcon(string $type): string
    {
        return match($type) {
            'document_validated' => 'document-check',
            'opportunity_won' => 'trophy',
            'opportunity_lost' => 'x-circle',
            'low_stock' => 'alert-triangle',
            'payment_received' => 'cash',
            'payment_overdue' => 'exclamation-triangle',
            'subscription_expiring' => 'calendar-x',
            'user_assigned' => 'user-plus',
            'comment_added' => 'message-circle',
            default => 'bell',
        };
    }
}
