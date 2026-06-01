<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

/**
 * Admin Activity Feed Controller
 */
class AdminActivityController extends Controller
{
    public function getFeed(Request $request)
    {
        $limit = $request->query('limit', 50);

        $activities = AuditLog::query()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user_id' => $log->user_id,
                    'action' => $log->action,
                    'entity_type' => $log->entity_type,
                    'entity_id' => $log->entity_id,
                    'changes' => $log->changes,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $activities,
        ]);
    }

    public function getNotifications(Request $request)
    {
        $limit = $request->query('limit', 20);

        $notifications = $request->user()
            ->notifications()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => class_basename($notification->type),
                    'title' => $notification->data['title'] ?? $notification->data['message'] ?? 'Thông báo mới',
                    'message' => $notification->data['message'] ?? null,
                    'is_read' => ! is_null($notification->read_at),
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
        ]);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Thông báo đã được đánh dấu là đã đọc.',
        ]);
    }

    public function markAllAsRead()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Tất cả thông báo đã được đánh dấu là đã đọc.',
        ]);
    }
}
