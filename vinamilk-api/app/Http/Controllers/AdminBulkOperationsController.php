<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Admin Bulk Operations Controller
 * Quản lý bulk operations (export, delete, update)
 */
class AdminBulkOperationsController extends Controller
{
    /**
     * Get bulk operations
     * GET /api/v1/admin/bulk-operations
     */
    public function index()
    {
        $operations = [
            [
                'id' => 'BULK-20250527-001',
                'entity_type' => 'Product',
                'operation' => 'export',
                'status' => 'completed',
                'total_items' => 1250,
                'processed_items' => 1250,
                'failed_items' => 0,
                'progress_percent' => 100,
                'download_url' => '/storage/exports/products-20250527.xlsx',
                'created_at' => now()->subDays(1)->format('Y-m-d H:i:s'),
                'completed_at' => now()->subDays(1)->addHours(1)->format('Y-m-d H:i:s'),
            ],
            [
                'id' => 'BULK-20250527-002',
                'entity_type' => 'Order',
                'operation' => 'update',
                'status' => 'processing',
                'total_items' => 500,
                'processed_items' => 324,
                'failed_items' => 2,
                'progress_percent' => 65,
                'download_url' => null,
                'created_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
                'completed_at' => null,
            ],
        ];

        return response()->json([
            'status' => 'success',
            'data' => $operations,
        ]);
    }

    /**
     * Get operation status
     * GET /api/v1/admin/bulk-operations/{id}
     */
    public function show($id)
    {
        $operation = [
            'id' => $id,
            'entity_type' => 'Product',
            'operation' => 'export',
            'status' => 'completed',
            'total_items' => 1250,
            'processed_items' => 1250,
            'failed_items' => 0,
            'progress_percent' => 100,
            'download_url' => '/storage/exports/products-20250527.xlsx',
            'created_at' => now()->format('Y-m-d H:i:s'),
            'completed_at' => now()->addHours(1)->format('Y-m-d H:i:s'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $operation,
        ]);
    }

    /**
     * Export data
     * POST /api/v1/admin/bulk-operations/export
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|string',
            'filters' => 'array',
        ]);

        $operationId = 'BULK-' . now()->format('YmdHis');
        $downloadUrl = "/storage/exports/{$validated['entity_type']}-{$operationId}.xlsx";

        $operation = [
            'id' => $operationId,
            'entity_type' => $validated['entity_type'],
            'operation' => 'export',
            'status' => 'completed',
            'total_items' => rand(100, 1000),
            'processed_items' => rand(100, 1000),
            'failed_items' => 0,
            'progress_percent' => 100,
            'download_url' => $downloadUrl,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'completed_at' => now()->addMinutes(2)->format('Y-m-d H:i:s'),
        ];

        AdminAuditLogController::log('export', $validated['entity_type'], 0);

        return response()->json([
            'status' => 'success',
            'message' => 'Dữ liệu được xuất thành công',
            'data' => $operation,
        ], 201);
    }

    /**
     * Delete multiple items
     * POST /api/v1/admin/bulk-operations/delete-multiple
     */
    public function deleteMultiple(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|string',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $operationId = 'BULK-' . now()->format('YmdHis');
        $totalItems = count($validated['ids']);

        $operation = [
            'id' => $operationId,
            'entity_type' => $validated['entity_type'],
            'operation' => 'delete',
            'status' => 'processing',
            'total_items' => $totalItems,
            'processed_items' => 0,
            'failed_items' => 0,
            'progress_percent' => 0,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'completed_at' => null,
        ];

        AdminAuditLogController::log('delete', $validated['entity_type'], 0, changes: [
            'bulk_delete' => $totalItems . ' items',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Bulk delete operation started',
            'data' => $operation,
        ], 201);
    }

    /**
     * Update multiple items
     * POST /api/v1/admin/bulk-operations/update-multiple
     */
    public function updateMultiple(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|string',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'updates' => 'required|array',
        ]);

        $operationId = 'BULK-' . now()->format('YmdHis');
        $totalItems = count($validated['ids']);

        $operation = [
            'id' => $operationId,
            'entity_type' => $validated['entity_type'],
            'operation' => 'update',
            'status' => 'processing',
            'total_items' => $totalItems,
            'processed_items' => 0,
            'failed_items' => 0,
            'progress_percent' => 0,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'completed_at' => null,
        ];

        AdminAuditLogController::log('update', $validated['entity_type'], 0, changes: [
            'bulk_update' => $totalItems . ' items',
            'updates' => $validated['updates'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Bulk update operation started',
            'data' => $operation,
        ], 201);
    }
}
