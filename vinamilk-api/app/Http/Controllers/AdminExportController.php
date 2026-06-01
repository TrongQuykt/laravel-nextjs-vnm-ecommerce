<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;
use App\Exports\ProductsExport;
use App\Exports\UsersExport;

class AdminExportController extends Controller
{
    /**
     * Export orders to Excel
     */
    public function exportOrders(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $status = $request->get('status');

        $query = Order::query();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return Excel::download(new OrdersExport($query), 'orders-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Export products to Excel
     */
    public function exportProducts(Request $request)
    {
        $category = $request->get('category');
        $brand = $request->get('brand');
        $status = $request->get('status');

        $query = Product::query();

        if ($category) {
            $query->where('category_id', $category);
        }

        if ($brand) {
            $query->where('brand_id', $brand);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return Excel::download(new ProductsExport($query), 'products-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Export users to Excel
     */
    public function exportUsers(Request $request)
    {
        $role = $request->get('role');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = User::query();

        if ($role) {
            $query->where('role', $role);
        }

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return Excel::download(new UsersExport($query), 'users-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Export orders to PDF
     */
    public function exportOrdersPdf(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $status = $request->get('status');

        $query = Order::with(['user', 'items.product'])
            ->orderBy('created_at', 'desc');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->get();

        $pdf = \PDF::loadView('exports.orders-pdf', compact('orders'));
        
        return $pdf->download('orders-' . now()->format('Y-m-d') . '.pdf');
    }
}
