<x-filament-widgets::widget>
    @php
        $pendingOrders = \App\Models\Order::where('status', 'pending')->count();
        $todayOrders = \App\Models\Order::where('created_at', '>=', \Carbon\Carbon::today())->count();
    @endphp
    <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 transition-all hover:shadow-md">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex-1">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Chào mừng trở lại, {{ auth()->user()->name }}! 👋
                </h2>
                <p class="mt-2 text-lg leading-8 text-slate-600">
                    Hôm nay là một ngày tuyệt vời để quản lý hệ thống Vinamilk Core. Bạn có <span class="font-semibold text-blue-600">{{ $pendingOrders }} đơn hàng chờ xử lý</span> và <span class="font-semibold text-green-600">{{ $todayOrders }} đơn hàng mới hôm nay</span>.
                </p>
                <div class="mt-6 flex items-center gap-x-6">
                    <a href="/admin/orders" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
                        Xem đơn hàng
                    </a>
                </div>
            </div>

            <div class="hidden lg:block">
                <div class="flex items-center gap-4 px-6 py-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex flex-col items-center">
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Thời gian</span>
                        <span class="text-xl font-bold text-slate-900">{{ now()->format('H:i') }}</span>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div class="flex flex-col items-center">
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Ngày</span>
                        <span class="text-xl font-bold text-slate-900">{{ now()->format('d/m') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decorative background elements -->
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-blue-50 opacity-20 blur-3xl"></div>
        <div class="absolute -bottom-10 left-10 h-40 w-40 rounded-full bg-teal-50 opacity-20 blur-3xl"></div>
    </div>
</x-filament-widgets::widget>
