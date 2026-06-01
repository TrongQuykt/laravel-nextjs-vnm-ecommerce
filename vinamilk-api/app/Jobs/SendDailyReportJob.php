<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDailyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $today = now()->startOfDay();
            
            // Get today's statistics
            $stats = [
                'date' => $today->format('d/m/Y'),
                'total_orders' => Order::where('created_at', '>=', $today)->count(),
                'total_revenue' => Order::where('created_at', '>=', $today)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_amount'),
                'completed_orders' => Order::where('created_at', '>=', $today)
                    ->where('status', 'completed')
                    ->count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
            ];

            // Get admin users
            $adminUsers = User::where('role', 'admin')
                ->orWhere('role', 'super_admin')
                ->orWhere('id', 1)
                ->get();

            // Send email to each admin
            foreach ($adminUsers as $admin) {
                Mail::to($admin->email)->send(new \App\Mail\DailyReportMail($stats));
            }

            Log::info('Daily report sent successfully', ['stats' => $stats]);
        } catch (\Exception $e) {
            Log::error('Failed to send daily report', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
