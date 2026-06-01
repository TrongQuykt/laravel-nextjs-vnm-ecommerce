<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Admin Reports Controller
 * Quản lý báo cáo
 */
class AdminReportsController extends Controller
{
    /**
     * Get report templates
     * GET /api/v1/admin/reports/templates
     */
    public function getTemplates()
    {
        $templates = [
            [
                'id' => 1,
                'name' => 'Báo Cáo Doanh Thu Hàng Tuần',
                'report_type' => 'sales',
                'metrics' => ['revenue', 'orders', 'avg_order_value'],
                'schedule' => 'weekly',
                'recipients' => ['manager@vinamilk.com'],
                'enabled' => true,
                'last_generated' => now()->subDays(2)->format('Y-m-d H:i:s'),
                'created_at' => now()->subMonths(3)->format('Y-m-d H:i:s'),
            ],
            [
                'id' => 2,
                'name' => 'Báo Cáo Khách Hàng Mới Hàng Tháng',
                'report_type' => 'customer',
                'metrics' => ['new_customers', 'total_spent', 'acquisition_cost'],
                'schedule' => 'monthly',
                'recipients' => ['marketing@vinamilk.com'],
                'enabled' => true,
                'last_generated' => now()->subDays(5)->format('Y-m-d H:i:s'),
                'created_at' => now()->subMonths(2)->format('Y-m-d H:i:s'),
            ],
            [
                'id' => 3,
                'name' => 'Báo Cáo Tồn Kho Hàng Ngày',
                'report_type' => 'inventory',
                'metrics' => ['stock_level', 'reorder_alert', 'turnover'],
                'schedule' => 'daily',
                'recipients' => ['warehouse@vinamilk.com'],
                'enabled' => true,
                'last_generated' => now()->format('Y-m-d H:i:s'),
                'created_at' => now()->subMonths(1)->format('Y-m-d H:i:s'),
            ],
        ];

        return response()->json([
            'status' => 'success',
            'data' => $templates,
        ]);
    }

    /**
     * Create report template
     * POST /api/v1/admin/reports/templates
     */
    public function createTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'report_type' => 'required|in:sales,customer,inventory,marketing,finance',
            'metrics' => 'required|array',
            'schedule' => 'in:daily,weekly,monthly',
            'recipients' => 'array',
            'recipients.*' => 'email',
        ]);

        $template = [
            'id' => rand(1, 1000),
            'name' => $validated['name'],
            'report_type' => $validated['report_type'],
            'metrics' => $validated['metrics'],
            'schedule' => $validated['schedule'],
            'recipients' => $validated['recipients'],
            'enabled' => true,
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];

        AdminAuditLogController::log('create', 'ReportTemplate', $template['id']);

        return response()->json([
            'status' => 'success',
            'message' => 'Mẫu báo cáo được tạo thành công',
            'data' => $template,
        ], 201);
    }

    /**
     * Generate report
     * POST /api/v1/admin/reports/generate
     */
    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|integer',
            'format' => 'required|in:pdf,excel,csv,json',
        ]);

        $reportId = 'RPT-' . now()->format('YmdHis');
        $downloadUrl = "/storage/reports/{$reportId}.{$validated['format']}";

        $report = [
            'id' => $reportId,
            'template_id' => $validated['template_id'],
            'title' => 'Generated Report ' . $reportId,
            'content' => [],
            'format' => $validated['format'],
            'file_url' => $downloadUrl,
            'file_size' => rand(100, 5000) * 1024, // KB
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => auth()->id(),
        ];

        AdminAuditLogController::log('create', 'Report', $reportId);

        return response()->json([
            'status' => 'success',
            'message' => 'Báo cáo được tạo thành công',
            'data' => $report,
        ], 201);
    }

    /**
     * Update report template
     * PUT /api/v1/admin/reports/templates/{id}
     */
    public function updateTemplate(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'report_type' => 'sometimes|in:sales,customer,inventory,marketing,finance',
            'metrics' => 'sometimes|array',
            'schedule' => 'sometimes|in:daily,weekly,monthly',
            'recipients' => 'sometimes|array',
            'recipients.*' => 'email',
            'enabled' => 'sometimes|boolean',
        ]);

        $template = array_merge([
            'id' => $id,
            'name' => 'Updated Report Template',
            'report_type' => 'sales',
            'metrics' => ['revenue', 'orders'],
            'schedule' => 'monthly',
            'recipients' => ['reports@vinamilk.com'],
            'enabled' => true,
            'last_generated' => now()->subDays(1)->format('Y-m-d H:i:s'),
            'created_at' => now()->subMonths(3)->format('Y-m-d H:i:s'),
        ], $validated);

        AdminAuditLogController::log('update', 'ReportTemplate', $id, auth()->id(), $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Mẫu báo cáo được cập nhật thành công',
            'data' => $template,
        ]);
    }

    /**
     * Delete report template
     * DELETE /api/v1/admin/reports/templates/{id}
     */
    public function deleteTemplate($id)
    {
        AdminAuditLogController::log('delete', 'ReportTemplate', $id);

        return response()->json([
            'status' => 'success',
            'message' => 'Mẫu báo cáo đã được xóa',
        ]);
    }

    /**
     * Schedule a report template
     * POST /api/v1/admin/reports/schedule
     */
    public function scheduleReport(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|integer',
            'schedule' => 'required|in:daily,weekly,monthly',
            'recipients' => 'array',
            'recipients.*' => 'email',
        ]);

        $schedule = [
            'id' => 'RPT-SCH-' . now()->format('YmdHis'),
            'template_id' => $validated['template_id'],
            'schedule' => $validated['schedule'],
            'recipients' => $validated['recipients'] ?? [],
            'enabled' => true,
            'next_run_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];

        AdminAuditLogController::log('create', 'ReportSchedule', $schedule['id'], auth()->id(), $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Báo cáo đã được lên lịch thành công',
            'data' => $schedule,
        ], 201);
    }

    /**
     * Get generated reports
     * GET /api/v1/admin/reports/generated?limit=20
     */
    public function getGeneratedReports(Request $request)
    {
        $limit = $request->query('limit', 20);

        $reports = [
            [
                'id' => 'RPT-20250527120000',
                'template_id' => 1,
                'title' => 'Weekly Sales Report - Week 21',
                'format' => 'pdf',
                'file_url' => '/storage/reports/RPT-20250527120000.pdf',
                'file_size' => 2456 * 1024,
                'generated_at' => now()->subDays(1)->format('Y-m-d H:i:s'),
                'generated_by' => 1,
            ],
            [
                'id' => 'RPT-20250526100000',
                'template_id' => 2,
                'title' => 'Monthly Customer Report - May 2025',
                'format' => 'excel',
                'file_url' => '/storage/reports/RPT-20250526100000.xlsx',
                'file_size' => 1200 * 1024,
                'generated_at' => now()->subDays(5)->format('Y-m-d H:i:s'),
                'generated_by' => 1,
            ],
        ];

        return response()->json([
            'status' => 'success',
            'data' => array_slice($reports, 0, $limit),
        ]);
    }
}
