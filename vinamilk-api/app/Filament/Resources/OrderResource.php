<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Traits\HasRolePermissions;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    use HasRolePermissions;

    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Bán hàng';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['id'];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return "Order #{$record->id}";
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['user', 'items.variant']);
    }

    public static function getOrderStepperPlaceholder()
    {
        return Forms\Components\Placeholder::make('order_flow_stepper')
            ->label('')
            ->content(function ($record) {
                if (!$record) return '-';
                
                $status = $record->status;
                
                $steps = [
                    'ordered' => ['label' => 'Đơn đã đặt', 'icon' => 'heroicon-o-shopping-bag'],
                    'pending' => ['label' => 'Chờ tiếp nhận', 'icon' => 'heroicon-o-clipboard-document-check'],
                    'processing' => ['label' => 'Chờ xử lý đóng gói', 'icon' => 'heroicon-o-archive-box'],
                    'packed' => ['label' => 'Đã đóng gói', 'icon' => 'heroicon-o-gift'],
                    'shipping' => ['label' => 'Đang giao hàng', 'icon' => 'heroicon-o-truck'],
                    'completed' => ['label' => 'Hoàn tất', 'icon' => 'heroicon-o-check-badge'],
                ];
                
                $statusHierarchy = [
                    'pending' => 1,
                    'processing' => 2,
                    'packed' => 3,
                    'shipping' => 4,
                    'completed' => 5,
                    'failed' => 5,
                    'cancelled' => 0,
                ];
                
                $currentStepIndex = $statusHierarchy[$status] ?? 1;
                
                if ($status === 'cancelled') {
                    return new \Illuminate\Support\HtmlString("
                        <div class='flex items-center justify-center p-6 bg-red-50/50 rounded-2xl border border-red-100 gap-6 shadow-sm mb-6 w-full'>
                            <div class='w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-600 shadow-sm'>
                                <svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'></path></svg>
                            </div>
                            <div>
                                <h3 class='font-black text-red-800 text-lg'>Đơn hàng đã bị hủy</h3>
                                <p class='text-xs text-red-600 font-semibold mt-0.5'>Đơn hàng này không còn hiệu lực vận hành.</p>
                            </div>
                        </div>
                    ");
                }
                
                $finalLabel = $status === 'failed' ? 'Giao hàng thất bại' : 'Giao thành công';
                
                $html = "<div class='flex items-center justify-between w-full p-8 gap-2 mb-8 select-none'>";
                
                $i = 0;
                foreach ($steps as $key => $step) {
                    $isActive = false;
                    $isCompleted = false;
                    
                    if ($key === 'ordered') {
                        $isCompleted = true;
                    } elseif ($key === 'completed') {
                        if ($status === 'completed' || $status === 'failed') {
                            $isActive = true;
                            $isCompleted = true;
                        }
                    } else {
                        $stepIndex = $statusHierarchy[$key] ?? 0;
                        if ($currentStepIndex > $stepIndex) {
                            $isCompleted = true;
                        } elseif ($currentStepIndex === $stepIndex) {
                            $isActive = true;
                        }
                    }
                    
                    $label = $key === 'completed' ? $finalLabel : $step['label'];
                    $colorClass = $isCompleted ? 'text-primary-600 font-extrabold' : ($isActive ? 'text-primary-500 font-bold' : 'text-gray-400 font-medium');
                    $bgClass = $isCompleted ? 'bg-primary-100 text-primary-600 border-primary-300' : ($isActive ? 'bg-primary-50 text-primary-500 border-primary-300 animate-pulse' : 'bg-gray-50 text-gray-400 border-gray-200');
                    
                    if ($key === 'completed' && $status === 'failed') {
                        $colorClass = 'text-red-600 font-extrabold';
                        $bgClass = 'bg-red-100 text-red-600 border-red-300';
                    } elseif ($key === 'completed' && $status === 'completed') {
                        $colorClass = 'text-green-600 font-extrabold';
                        $bgClass = 'bg-green-100 text-green-600 border-green-300';
                    }
                    
                    if ($i > 0) {
                        $lineBg = $isCompleted ? 'bg-primary-500' : 'bg-gray-200';
                        $html .= "<div class='flex-1 h-1.5 mx-2 rounded-full {$lineBg} transition-all duration-500'></div>";
                    }
                    
                    $html .= "
                        <div class='flex flex-col items-center text-center gap-2.5 z-10'>
                            <div class='w-14 h-14 rounded-full border-2 {$bgClass} flex items-center justify-center shadow-sm transition-all duration-300'>
                                <svg class='w-7 h-7' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                    ";
                    
                    if ($key === 'ordered') {
                        $html .= "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'></path>";
                    } elseif ($key === 'pending') {
                        $html .= "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'></path>";
                    } elseif ($key === 'processing') {
                        $html .= "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'></path>";
                    } elseif ($key === 'packed') {
                        $html .= "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'></path>";
                    } elseif ($key === 'shipping') {
                        $html .= "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z'></path><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V15a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'></path>";
                    } elseif ($key === 'completed') {
                        if ($status === 'failed') {
                            $html .= "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'></path>";
                        } else {
                            $html .= "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 00.906 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.906 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.906 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.906 3.42 3.42 0 01-3.138-3.138z'></path>";
                        }
                    }
                    
                    $html .= "
                                </svg>
                            </div>
                            <span class='text-[13px] tracking-tight {$colorClass}'>{$label}</span>
                        </div>
                    ";
                    $i++;
                }
                
                $html .= "</div>";
                return new \Illuminate\Support\HtmlString($html);
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        self::getOrderStepperPlaceholder()
                            ->columnSpanFull(),

                        Forms\Components\Section::make('Cốt lõi')
                            ->schema([
                                Forms\Components\TextInput::make('order_number')
                                    ->readOnly()
                                    ->label('Mã đơn hàng'),
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->disabled()
                                    ->label('Khách hàng'),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'Chờ tiếp nhận',
                                        'processing' => 'Chờ xử lý đóng gói',
                                        'packed' => 'Đã đóng gói',
                                        'shipping' => 'Đang giao hàng',
                                        'completed' => 'Giao hàng thành công',
                                        'failed' => 'Giao hàng thất bại',
                                        'cancelled' => 'Đã hủy',
                                    ])
                                    ->required()
                                    ->label('Trạng thái đơn hàng'),
                                Forms\Components\Placeholder::make('payment_status_display')
                                    ->label('Trạng thái thanh toán')
                                    ->content(fn ($record) => match ($record?->payment_status) {
                                        'unpaid' => new \Illuminate\Support\HtmlString('<span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold uppercase">Chưa thanh toán</span>'),
                                        'pending_payment' => new \Illuminate\Support\HtmlString('<span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-bold uppercase">Chờ xác nhận</span>'),
                                        'paid' => new \Illuminate\Support\HtmlString('<span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold uppercase">Đã thanh toán</span>'),
                                        'failed' => new \Illuminate\Support\HtmlString('<span class="px-2 py-1 bg-red-200 text-red-800 rounded text-xs font-bold uppercase">Thanh toán lỗi</span>'),
                                        'refunded' => new \Illuminate\Support\HtmlString('<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-bold uppercase">Đã hoàn tiền</span>'),
                                        default => $record?->payment_status,
                                    }),
                                Forms\Components\Placeholder::make('care_info')
                                    ->label('Thông tin Vinamilk Care')
                                    ->content(function ($record) {
                                        if (!$record || $record->order_type !== 'care') return '-';
                                        $sub = $record->careSubscription;
                                        if (!$sub) return 'Không tìm thấy subscription';
                                        return new \Illuminate\Support\HtmlString("
                                            <div class='space-y-1 text-sm'>
                                                <p><b>Subscription ID:</b> #{$sub->id}</p>
                                                <p><b>Gói:</b> " . ucfirst($sub->tier) . "</p>
                                                <p><b>Số lần giao:</b> {$sub->delivery_count} lần</p>
                                                <p><b>Số lượng/kỳ:</b> {$sub->quantity_per_delivery} SP</p>
                                                <p><b>Ngày giao đầu:</b> " . ($sub->first_delivery_date ? $sub->first_delivery_date->format('d/m/Y') : '-') . "</p>
                                                <p><b>Trạng thái:</b> " . ucfirst($sub->status) . "</p>
                                            </div>
                                        ");
                                    })
                                    ->visible(fn ($record) => $record && $record->order_type === 'care'),
                            ])->columnSpan(2),

                        Forms\Components\Section::make('Tài chính')
                            ->schema([
                                Forms\Components\Placeholder::make('total_amount')
                                    ->label('Total amount')
                                    ->content(fn ($record) => $record ? number_format($record->total_amount) . 'đ' : '0đ'),
                                Forms\Components\Placeholder::make('voucher_code')
                                    ->label('Mã Voucher')
                                    ->content(fn ($record) => $record && $record->voucher_code ? $record->voucher_code : 'Không có'),
                                Forms\Components\Placeholder::make('discount_amount')
                                    ->label('Discount amount')
                                    ->content(fn ($record) => $record ? number_format($record->discount_amount) . 'đ' : '0đ'),
                                Forms\Components\Placeholder::make('shipping_cost')
                                    ->label('Shipping cost')
                                    ->content(fn ($record) => $record ? number_format($record->shipping_cost) . 'đ' : '0đ'),
                                Forms\Components\TextInput::make('payment_method')
                                    ->label('Payment method')
                                    ->readOnly()
                                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                            ])->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Thông tin nhận hàng')
                    ->schema([
                        Forms\Components\Placeholder::make('delivery_info')
                            ->label('Hình thức & Địa chỉ')
                            ->content(function ($record) {
                                if (!$record) return '-';
                                $addr = $record->shipping_address;
                                $type = $record->delivery_type === 'shipping' ? 'GIAO TẬN NƠI' : 'NHẬN TẠI CỬA HÀNG';
                                
                                $html = "<div class='space-y-1'>";
                                $html .= "<p class='font-black text-primary-600'>$type</p>";
                                
                                if ($record->delivery_type === 'shipping') {
                                    $html .= "<p><b>Phương thức:</b> " . ($record->shipping_method_name ?? 'Tiêu chuẩn') . "</p>";
                                    $html .= "<p><b>Người nhận:</b> " . ($addr['last_name'] ?? '') . " " . ($addr['first_name'] ?? '') . "</p>";
                                    $html .= "<p><b>SĐT:</b> " . ($addr['phone'] ?? '-') . "</p>";
                                    $html .= "<p><b>Địa chỉ:</b> " . ($addr['detail'] ?? '') . ", " . ($addr['ward'] ?? '') . ", " . ($addr['district'] ?? '') . ", " . ($addr['city'] ?? '') . "</p>";
                                } else {
                                    $receiverName = $record->shipping_address['name'] ?? '-';
                                    $receiverPhone = $record->shipping_address['phone'] ?? '-';
                                    $html .= "<p><b>Người nhận:</b> {$receiverName}</p>";
                                    $html .= "<p><b>SĐT:</b> {$receiverPhone}</p>";
                                    $html .= "<p><b>Thời gian:</b> {$record->pickup_time}</p>";
                                }
                                $html .= "</div>";
                                
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ])->columns(1),

                Forms\Components\Section::make('Chi tiết kiện hàng')
                    ->schema([
                        Forms\Components\Placeholder::make('packages')
                            ->label('Danh sách kiện hàng')
                            ->content(function ($record) {
                                if (!$record) return '-';
                                $items = $record->items;
                                $packages = $items->groupBy('package_number');
                                
                                $html = "<div class='space-y-6'>";
                                foreach ($packages as $pkgNum => $pkgItems) {
                                    $html .= "<div class='border rounded-lg p-5 bg-gray-50/50 shadow-sm'>";
                                    $html .= "<div class='flex justify-between border-b pb-3 mb-3'>";
                                    $html .= "<span class='font-black text-sm uppercase text-primary-600 tracking-wider'>Kiện hàng: #{$pkgNum}</span>";
                                    // If this is a Care order, try to show scheduled delivery date for this package
                                    if ($record->order_type === 'care' && $record->careSubscription) {
                                        // extract FN index from package number (e.g. ORD123FN2)
                                        if (preg_match('/FN(\d+)$/', $pkgNum, $m)) {
                                            $idx = (int)$m[1];
                                            $delivery = $record->careSubscription->deliveries->firstWhere('delivery_index', $idx);
                                            if ($delivery) {
                                                $html .= "<div class='text-sm text-gray-600'>" . ($delivery->scheduled_date ? $delivery->scheduled_date->format('d/m/Y') : '') . "</div>";
                                            }
                                        }
                                    }
                                    $html .= "</div>";
                                    
                                    foreach ($pkgItems as $item) {
                                        $isGiftPackage = str_contains($pkgNum, 'GIFT');
                                        $imageUrl = $item->image;
                                        
                                        // If this is a greeting card package, use gift_default.webp
                                        if ($isGiftPackage) {
                                            $imageUrl = asset('storage/gift_default.webp');
                                        } elseif ($imageUrl) {
                                            if (!str_starts_with($imageUrl, 'http')) {
                                                $imageUrl = asset('storage/' . ltrim($imageUrl, '/'));
                                            }
                                        } else {
                                            $imageUrl = 'https://placehold.co/100x100?text=Vinamilk';
                                        }
                                        
                                        $isGift = $item->price == 0 || $item->original_price == 0;
                                        $giftBadge = $isGift ? "<span class='px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs font-black uppercase ml-2 shadow-sm'>Quà tặng</span>" : "";
                                        
                                        $priceHtml = $isGift 
                                            ? "<span class='text-green-600 font-black text-sm'>Miễn phí</span>"
                                            : "<div class='text-right'>
                                                <span class='text-xs line-through text-gray-400 block'>" . number_format($item->original_price) . "đ</span>
                                                <span class='text-sm font-black text-primary-600 block'>" . number_format($item->price) . "đ</span>
                                               </div>";

                                        $html .= "<div class='flex items-center justify-between py-3 border-b last:border-b-0'>";
                                        $html .= "  <div class='flex items-center gap-4'>";
                                        $html .= "    <img src='{$imageUrl}' class='w-16 h-16 object-contain border rounded bg-white p-1 shadow-sm' />";
                                        $html .= "    <div>";
                                        $html .= "      <h4 class='font-bold text-gray-800 text-[14px]'>{$item->product_name}</h4>";
                                        $html .= "      <p class='text-xs text-gray-500 mt-0.5'>Phân loại: " . ($item->variant_name ?? 'Mặc định') . " | Thể tích: " . ($item->volume ?? '-') . " | Quy cách: " . ($item->packing_type ?? '-') . "</p>";
                                        $html .= "      <div class='flex items-center mt-1 text-xs text-gray-600 font-medium'>";
                                        $html .= "        <span>Số lượng: x{$item->quantity}</span>";
                                        $html .= "        {$giftBadge}";
                                        $html .= "      </div>";
                                        $html .= "    </div>";
                                        $html .= "  </div>";
                                        $html .= "  <div class='flex items-center gap-4'>{$priceHtml}</div>";
                                        $html .= "</div>";
                                    }
                                    $html .= "</div>";
                                }
                                $html .= "</div>";
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ]),



                Forms\Components\Section::make('Ghi chú điều hành')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->placeholder('Nhập ghi chú vận hành...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('order_type')
                    ->label('Loại')
                    ->colors(['primary' => 'care', 'gray' => 'standard'])
                    ->formatStateUsing(fn ($state) => $state === 'care' ? 'Vinamilk Care' : 'Thường'),
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Mã đơn')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->shipping_address['phone'] ?? ''),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Tổng tiền')
                    ->money('VND')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\BadgeColumn::make('delivery_type')
                    ->label('Hình thức')
                    ->colors(['primary' => 'shipping', 'gray' => 'pickup'])
                    ->formatStateUsing(fn ($state) => $state === 'shipping' ? 'Giao tận nơi' : 'Tại quầy'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'primary' => 'pending',
                        'info' => 'processing',
                        'teal' => 'packed',
                        'warning' => 'shipping',
                        'success' => 'completed',
                        'danger' => ['cancelled', 'failed'],
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Chờ tiếp nhận',
                        'processing' => 'Chờ đóng gói',
                        'packed' => 'Đã đóng gói',
                        'shipping' => 'Đang giao hàng',
                        'completed' => 'Giao hàng thành công',
                        'failed' => 'Giao hàng thất bại',
                        'cancelled' => 'Đã hủy',
                        default => $state
                    }),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Thanh toán')
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => ['pending_payment', 'failed'],
                        'success' => 'paid',
                        'info' => 'refunded',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'unpaid' => 'Chưa thanh toán',
                        'pending_payment' => 'Chờ xác nhận',
                        'paid' => 'Đã thanh toán',
                        'failed' => 'Lỗi',
                        'refunded' => 'Đã hoàn tiền',
                        default => $state
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Chờ tiếp nhận',
                        'processing' => 'Chờ đóng gói',
                        'packed' => 'Đã đóng gói',
                        'shipping' => 'Đang giao hàng',
                        'completed' => 'Giao hàng thành công',
                        'failed' => 'Giao hàng thất bại',
                        'cancelled' => 'Đã hủy',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Thanh toán')
                    ->options([
                        'unpaid' => 'Chưa thanh toán',
                        'pending_payment' => 'Chờ xác nhận',
                        'paid' => 'Đã thanh toán',
                        'failed' => 'Lỗi',
                        'refunded' => 'Đã hoàn tiền',
                    ]),
                Tables\Filters\SelectFilter::make('order_type')
                    ->label('Loại đơn')
                    ->options([
                        'standard' => 'Thường',
                        'care' => 'Vinamilk Care',
                    ]),
                Tables\Filters\SelectFilter::make('delivery_type')
                    ->label('Hình thức')
                    ->options([
                        'shipping' => 'Giao tận nơi',
                        'pickup' => 'Tại quầy',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->label('Ngày tạo')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Từ ngày'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Đến ngày'),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['from'] ?? null) {
                            $query->where('created_at', '>=', $data['from']);
                        }
                        if ($data['until'] ?? null) {
                            $query->where('created_at', '<=', $data['until']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\Action::make('print')
                //     ->label('In đơn')
                //     ->icon('heroicon-o-printer')
                //     ->url(fn ($record) => route('orders.print', $record))
                //     ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_processing')
                        ->label('Đánh dấu đang xử lý')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $record->update(['status' => 'processing']);
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('mark_packed')
                        ->label('Đánh dấu đã đóng gói')
                        ->icon('heroicon-o-archive-box')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (in_array($record->status, ['pending', 'processing'])) {
                                    $record->update(['status' => 'packed']);
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('mark_shipping')
                        ->label('Đánh dấu đang giao')
                        ->icon('heroicon-o-truck')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (in_array($record->status, ['pending', 'processing', 'packed'])) {
                                    $record->update(['status' => 'shipping']);
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('mark_completed')
                        ->label('Đánh dấu hoàn thành')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (in_array($record->status, ['pending', 'processing', 'packed', 'shipping'])) {
                                    $record->update(['status' => 'completed']);
                                }
                            }
                        }),
                ]),
            ])
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
