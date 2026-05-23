<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Credentials Card -->
        <div class="bg-white dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-black text-lg text-primary-700 dark:text-primary-400 uppercase tracking-wider mb-4">Cấu hình GHN Sandbox</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-gray-400 dark:text-gray-500 block font-semibold text-[11px] uppercase">ENDPOINT URL</span>
                        <code class="bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 px-2 py-0.5 rounded text-xs block truncate mt-1 text-gray-700 dark:text-gray-300">
                            {{ (new \App\Services\GhnService())->baseUrl }}
                        </code>
                    </div>
                    <div>
                        <span class="text-gray-400 dark:text-gray-500 block font-semibold text-[11px] uppercase">API TOKEN</span>
                        <code class="bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 px-2 py-0.5 rounded text-xs block truncate mt-1 text-gray-700 dark:text-gray-300">
                            {{ (new \App\Services\GhnService())->token }}
                        </code>
                    </div>
                    <div>
                        <span class="text-gray-400 dark:text-gray-500 block font-semibold text-[11px] uppercase">SHOP ID</span>
                        <code class="bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 px-2 py-0.5 rounded text-xs block truncate mt-1 text-gray-700 dark:text-gray-300">
                            {{ (new \App\Services\GhnService())->shopId }}
                        </code>
                    </div>
                </div>
            </div>
            <div class="mt-6 border-t border-gray-100 dark:border-gray-800 pt-4">
                <p class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold uppercase">Trạng thái cấu hình</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-xs font-black text-green-600 dark:text-green-400 uppercase">
                        {{ str_contains((new \App\Services\GhnService())->baseUrl, 'dev-online-gateway') ? 'Đang hoạt động (Sandbox)' : 'Đang hoạt động (Production)' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Connection Test Card -->
        <div class="bg-white dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm md:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-black text-lg text-primary-700 dark:text-primary-400 uppercase tracking-wider">Kiểm tra kết nối API</h3>
                    @if($connectionStatus === 'success')
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-black uppercase shadow-sm">KẾT NỐI TỐT</span>
                    @elseif($connectionStatus === 'failed')
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-black uppercase shadow-sm">LỖI KẾT NỐI</span>
                    @else
                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-black uppercase shadow-sm">CHƯA KIỂM TRA</span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold mb-4 leading-relaxed">
                    Nhấn nút kiểm tra để thực hiện một cuộc gọi API thực tế tới cổng dịch vụ GHN Sandbox để kiểm định tính khả dụng và xác thực của cấu hình.
                </p>

                @if(!empty($ghnProvinces))
                    <div class="bg-gray-50 dark:bg-gray-900 border border-dashed border-gray-200 dark:border-gray-800 p-4 rounded-xl space-y-2 mt-2">
                        <p class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Dữ liệu tỉnh thành mẫu từ GHN:</p>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-xs">
                            @foreach($ghnProvinces as $p)
                                <div class="bg-white dark:bg-gray-950 border border-gray-100 dark:border-gray-800 p-2 rounded shadow-sm text-center">
                                    <span class="font-bold text-gray-800 dark:text-gray-200 block truncate">{{ $p['ProvinceName'] }}</span>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">ID: {{ $p['ProvinceID'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6 border-t border-gray-100 dark:border-gray-800 pt-4 text-right">
                <x-filament::button wire:click="testConnection" class="bg-primary-600 hover:bg-primary-700 shadow-sm font-bold text-sm">
                    Kiểm tra kết nối API GHN
                </x-filament::button>
            </div>
        </div>
    </div>

    <!-- GHN Shipped Orders Table -->
    <div class="bg-white dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden mt-8">
        <div class="p-6 border-b border-gray-100 dark:border-gray-800">
            <h3 class="font-black text-lg text-primary-700 dark:text-primary-400 uppercase tracking-wider">Danh sách Đơn hàng Giao qua GHN</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold mt-1">Hiển thị các đơn hàng trong hệ thống được cấu hình giao nhận qua Giao Hàng Nhanh.</p>
        </div>

        @if(count($ghnOrders) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800 text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                            <th class="p-4 pl-6">Mã đơn hàng</th>
                            <th class="p-4">Khách hàng</th>
                            <th class="p-4">Mã vận đơn GHN</th>
                            <th class="p-4">Tiền hàng (COD)</th>
                            <th class="p-4">Phí ship</th>
                            <th class="p-4">Trạng thái hệ thống</th>
                            <th class="p-4 pr-6 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs text-gray-700 dark:text-gray-300">
                        @foreach($ghnOrders as $order)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                <td class="p-4 pl-6 font-bold text-gray-900 dark:text-white">#{{ $order->order_number }}</td>
                                <td class="p-4 font-medium">{{ $order->user?->name ?? 'Vãng lai' }}</td>
                                <td class="p-4">
                                    @if($order->tracking_number)
                                        <span class="px-2 py-0.5 bg-orange-50 dark:bg-orange-950/20 border border-orange-200 dark:border-orange-800/40 text-orange-700 dark:text-orange-400 rounded font-black tracking-wider uppercase text-[10px]">
                                            {{ $order->tracking_number }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500 italic">Chưa tạo vận đơn</span>
                                    @endif
                                </td>
                                <td class="p-4 font-bold text-gray-900 dark:text-white">{{ number_format($order->total_amount) }}đ</td>
                                <td class="p-4 font-bold text-primary-600 dark:text-primary-400">{{ number_format($order->shipping_cost) }}đ</td>
                                <td class="p-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase shadow-sm
                                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-950/20 dark:text-green-400' : '' }}
                                        {{ $order->status === 'shipping' ? 'bg-primary-100 text-primary-700 dark:bg-primary-950/20 dark:text-primary-400' : '' }}
                                        {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400' : '' }}
                                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950/20 dark:text-yellow-400' : '' }}
                                        {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700 dark:bg-red-950/20 dark:text-red-400' : '' }}
                                    ">
                                        {{ match($order->status) {
                                            'pending' => 'Chờ xác nhận',
                                            'processing' => 'Đang đóng gói',
                                            'shipping' => 'Đang giao hàng',
                                            'completed' => 'Giao thành công',
                                            'cancelled' => 'Đã hủy',
                                            default => $order->status
                                        } }}
                                    </span>
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    @if($order->tracking_number)
                                        <x-filament::button 
                                            wire:click="syncOrderStatus({{ $order->id }})" 
                                            class="bg-orange-600 hover:bg-orange-700 font-bold text-[11px] shadow-sm py-1 px-3"
                                        >
                                            Đồng bộ hành trình
                                        </x-filament::button>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500 text-[11px]">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center text-gray-400 dark:text-gray-500 font-medium">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2' d='M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'></path></svg>
                Không có đơn hàng nào giao qua GHN.
            </div>
        @endif
    </div>
</x-filament-panels::page>
