<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

/**
 * Admin Audit Log Controller
 * Quản lý audit logs
 */
class AdminAuditLogController extends Controller
{
    /**
     * Get audit logs
     * GET /api/v1/admin/audit-logs?page=1&limit=50
     */
    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 50);
        $action = $request->query('action');
        $entityType = $request->query('entity_type');
        $userId = $request->query('user_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = AuditLog::query();

        if ($action) {
            $query->where('action', $action);
        }

        if ($entityType) {
            $query->where('entity_type', $entityType);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $total = $query->count();
        $logs = $query->orderByDesc('created_at')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs,
            'meta' => [
                'page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
            ],
        ]);
    }

    /**
     * Export audit logs
     * GET /api/v1/admin/audit-logs/export
     */
    public function export(Request $request)
    {
        $logs = AuditLog::orderByDesc('created_at')
            ->limit(10000)
            ->get();

        $filename = 'audit-logs-' . now()->format('Y-m-d-H-i-s') . '.csv';
        $path = storage_path("app/exports/{$filename}");

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        // Create CSV
        $file = fopen($path, 'w');
        fputcsv($file, ['ID', 'User ID', 'Action', 'Entity Type', 'Entity ID', 'Changes', 'IP Address', 'Created At']);

        foreach ($logs as $log) {
            fputcsv($file, [
                $log->id,
                $log->user_id,
                $log->action,
                $log->entity_type,
                $log->entity_id,
                json_encode($log->changes),
                $log->ip_address,
                $log->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($file);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * Create audit log
     */
    public static function log($action, $entityType, $entityId, $userId = null, $changes = null, $ipAddress = null)
    {
        return AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'changes' => $changes,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
