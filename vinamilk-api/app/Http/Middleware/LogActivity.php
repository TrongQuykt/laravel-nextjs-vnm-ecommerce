<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log all authenticated requests
        if (Auth::check()) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    protected function logActivity(Request $request, Response $response): void
    {
        $user = Auth::user();
        
        if (!$user) {
            return;
        }

        // Skip logging for activity logs themselves to avoid infinite loops
        if (str_contains($request->path(), 'activity-logs')) {
            return;
        }

        // Skip logging for asset requests
        if (str_contains($request->path(), 'storage') || str_contains($request->path(), 'assets')) {
            return;
        }

        $action = $this->determineAction($request);
        $resourceType = $this->determineResourceType($request);
        $resourceId = $this->determineResourceId($request);
        $description = $this->generateDescription($request, $action, $resourceType);

        try {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
            ]);
        } catch (\Exception $e) {
            // Silently fail to avoid breaking the application
            \Illuminate\Support\Facades\Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }

    protected function determineAction(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        // Check for specific actions based on URL patterns
        if (str_contains($path, '/login')) {
            return 'login';
        }
        
        if (str_contains($path, '/logout')) {
            return 'logout';
        }

        if (str_contains($path, '/create')) {
            return 'create';
        }

        if (str_contains($path, '/edit')) {
            return 'update';
        }

        if ($method === 'DELETE' || str_contains($path, '/delete')) {
            return 'delete';
        }

        if (str_contains($path, '/export')) {
            return 'export';
        }

        if (str_contains($path, '/import')) {
            return 'import';
        }

        // Default to view for GET requests
        if ($method === 'GET') {
            return 'view';
        }

        return 'unknown';
    }

    protected function determineResourceType(Request $request): ?string
    {
        $path = $request->path();

        // Extract resource type from URL
        if (preg_match('/\/([a-z-]+)\/\d+/', $path, $matches)) {
            return ucfirst(str_replace('-', '', $matches[1]));
        }

        if (preg_match('/\/([a-z-]+)\/create/', $path, $matches)) {
            return ucfirst(str_replace('-', '', $matches[1]));
        }

        if (preg_match('/\/([a-z-]+)\/edit/', $path, $matches)) {
            return ucfirst(str_replace('-', '', $matches[1]));
        }

        // Common Filament resource patterns
        $resourceMap = [
            'products' => 'Product',
            'orders' => 'Order',
            'users' => 'User',
            'categories' => 'Category',
            'brands' => 'Brand',
            'vouchers' => 'Voucher',
            'promotions' => 'Promotion',
            'rewards' => 'Reward',
            'blog-posts' => 'BlogPost',
            'banners' => 'Banner',
            'stores' => 'Store',
            'activity-logs' => 'ActivityLog',
        ];

        foreach ($resourceMap as $pattern => $type) {
            if (str_contains($path, $pattern)) {
                return $type;
            }
        }

        return null;
    }

    protected function determineResourceId(Request $request): ?int
    {
        $path = $request->path();

        if (preg_match('/\/(\d+)/', $path, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    protected function generateDescription(Request $request, string $action, ?string $resourceType): string
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
        $resourceLabel = $resourceType ?? 'tài nguyên';

        return "{$actionLabel} {$resourceLabel}";
    }
}
