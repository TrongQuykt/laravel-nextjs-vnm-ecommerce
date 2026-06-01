<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log an activity
     */
    public static function log(string $action, string $resourceType, ?int $resourceId = null, ?string $description = null, ?array $oldValues = null, ?array $newValues = null): ActivityLog
    {
        $user = Auth::user();
        
        if (!$user) {
            throw new \Exception('User must be authenticated to log activity');
        }

        if (!$description) {
            $description = self::generateDescription($action, $resourceType);
        }

        return ActivityLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
        ]);
    }

    /**
     * Generate description from action and resource type
     */
    protected static function generateDescription(string $action, string $resourceType): string
    {
        $actionLabels = [
            'create' => 'Tạo mới',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
            'view' => 'Xem',
            'login' => 'Đăng nhập',
            'logout' => 'Đăng xuất',
            'export' => 'Xuất dữ liệu',
            'import' => 'Nhập dữ liệu',
        ];

        $actionLabel = $actionLabels[$action] ?? $action;
        
        return "{$actionLabel} {$resourceType}";
    }

    /**
     * Log create action
     */
    public static function logCreate(string $resourceType, int $resourceId, ?array $newValues = null): ActivityLog
    {
        return self::log('create', $resourceType, $resourceId, null, null, $newValues);
    }

    /**
     * Log update action
     */
    public static function logUpdate(string $resourceType, int $resourceId, ?array $oldValues = null, ?array $newValues = null): ActivityLog
    {
        return self::log('update', $resourceType, $resourceId, null, $oldValues, $newValues);
    }

    /**
     * Log delete action
     */
    public static function logDelete(string $resourceType, int $resourceId, ?array $oldValues = null): ActivityLog
    {
        return self::log('delete', $resourceType, $resourceId, null, $oldValues, null);
    }

    /**
     * Log view action
     */
    public static function logView(string $resourceType, ?int $resourceId = null): ActivityLog
    {
        return self::log('view', $resourceType, $resourceId);
    }
}
