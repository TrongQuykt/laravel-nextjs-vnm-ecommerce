<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\ShippingMethod;
use Filament\Notifications\Notification;

class GhnDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Quản trị GHN';

    protected static ?string $title = 'Quản trị GHN (Sandbox)';

    protected static string $view = 'filament.pages.ghn-dashboard';
    
    // Custom states
    public $connectionStatus = 'unknown';
    public $ghnProvinces = [];
    public $ghnOrders = [];
    
    public function mount()
    {
        $this->loadGhnOrders();
    }
    
    public function loadGhnOrders()
    {
        $this->ghnOrders = Order::where('delivery_type', 'shipping')
            ->whereNotNull('tracking_number')
            ->where('tracking_number', '!=', '')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();
    }
    
    public function testConnection()
    {
        $ghn = new \App\Services\GhnService();
        
        try {
            $response = Http::withHeaders(['Token' => $ghn->token])
                ->get("{$ghn->baseUrl}/master-data/province");
                
            if ($response->successful()) {
                $this->connectionStatus = 'success';
                $this->ghnProvinces = array_slice($response->json()['data'] ?? [], 0, 5);
                Notification::make()
                    ->title('Kết nối GHN thành công!')
                    ->success()
                    ->send();
            } else {
                $this->connectionStatus = 'failed';
                Notification::make()
                    ->title('Kết nối thất bại. Lỗi API: ' . $response->status())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            $this->connectionStatus = 'failed';
            Notification::make()
                ->title('Kết nối thất bại: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function syncOrderStatus($orderId)
    {
        $order = Order::find($orderId);
        if (!$order || !$order->tracking_number) {
            Notification::make()
                ->title('Đơn hàng không có mã vận đơn GHN.')
                ->warning()
                ->send();
            return;
        }
        
        $ghn = new \App\Services\GhnService();
        
        try {
            $response = Http::withHeaders([
                'Token' => $ghn->token,
                'Content-Type' => 'application/json'
            ])->post("{$ghn->baseUrl}/v2/shipping-order/detail", [
                'order_code' => $order->tracking_number
            ]);
            
            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                $ghnStatus = $data['status'] ?? null;
                
                // Map status from GHN to internal
                // GHN status list: ready_to_pick, picking, delivering, delivered, cancel, etc.
                $statusMap = [
                    'ready_to_pick' => 'processing',
                    'picking' => 'processing',
                    'delivering' => 'shipping',
                    'delivered' => 'completed',
                    'cancel' => 'cancelled',
                ];
                
                if ($ghnStatus && isset($statusMap[$ghnStatus])) {
                    $order->status = $statusMap[$ghnStatus];
                }
                
                $order->notes = ($order->notes ? $order->notes . "\n" : "") . 
                    "Đã đồng bộ GHN: Trạng thái hiện tại: " . ($ghnStatus ?? 'Không rõ') . " vào lúc " . now()->toDateTimeString();
                $order->save();
                
                $this->loadGhnOrders();
                
                Notification::make()
                    ->title('Đồng bộ hành trình từ GHN thành công!')
                    ->body('Trạng thái hiện tại: ' . ($ghnStatus ?? 'Không rõ'))
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Không thể lấy chi tiết vận đơn từ GHN.')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Lỗi đồng bộ: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
