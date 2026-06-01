<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogisticsResource\Pages;
use App\Filament\Traits\HasRolePermissions;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LogisticsResource extends Resource
{
    use HasRolePermissions;
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Bán hàng';

    protected static ?string $navigationLabel = 'Quản lý Vận chuyển';

    protected static ?string $modelLabel = 'Vận chuyển';

    protected static ?string $pluralModelLabel = 'Quản lý Vận chuyển';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        // Only show orders that are packed, shipping, completed, or failed
        return parent::getEloquentQuery()->whereIn('status', ['packed', 'shipping', 'completed', 'failed']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        // Stepper full-width at the top
                        OrderResource::getOrderStepperPlaceholder()
                            ->columnSpanFull(),

                        Forms\Components\Section::make('Cốt lõi Vận chuyển')
                            ->schema([
                                Forms\Components\TextInput::make('order_number')
                                    ->readOnly()
                                    ->label('Mã đơn hàng'),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'packed' => 'Đã đóng gói / Chờ giao hàng',
                                        'shipping' => 'Đang giao hàng',
                                        'completed' => 'Giao hàng thành công',
                                        'failed' => 'Giao hàng thất bại',
                                    ])
                                    ->required()
                                    ->label('Trạng thái vận chuyển'),
                                Forms\Components\TextInput::make('tracking_number')
                                    ->label('Mã vận đơn (GHN)')
                                    ->readOnly()
                                    ->placeholder('Chưa tạo vận đơn GHN'),
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->disabled()
                                    ->label('Khách hàng'),
                            ])->columnSpan(2),

                        Forms\Components\Section::make('Chi tiết Vận chuyển')
                            ->schema([
                                Forms\Components\Placeholder::make('shipping_method_name')
                                    ->label('Đơn vị vận chuyển')
                                    ->content(function ($record) {
                                        if (!$record) return 'Giao hàng tiêu chuẩn';
                                        $name = $record->shipping_method_name ?: 'Giao hàng tiêu chuẩn';
                                        if ($record->status === 'shipping' || $record->status === 'completed' || $record->tracking_number) {
                                            return "Giao Hàng Nhanh (GHN) - " . $name;
                                        }
                                        return $name;
                                    }),
                                Forms\Components\DatePicker::make('expected_delivery_date')
                                    ->label('Ngày dự kiến giao hàng'),
                                Forms\Components\Placeholder::make('payment_status_display')
                                    ->label('Thanh toán')
                                    ->content(fn ($record) => match ($record?->payment_status) {
                                        'unpaid' => new \Illuminate\Support\HtmlString('<span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold uppercase">Chưa thanh toán</span>'),
                                        'pending_payment' => new \Illuminate\Support\HtmlString('<span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-bold uppercase">Chờ xác nhận</span>'),
                                        'paid' => new \Illuminate\Support\HtmlString('<span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold uppercase">Đã thanh toán</span>'),
                                        'failed' => new \Illuminate\Support\HtmlString('<span class="px-2 py-1 bg-red-200 text-red-800 rounded text-xs font-bold uppercase">Thanh toán lỗi</span>'),
                                        'refunded' => new \Illuminate\Support\HtmlString('<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-bold uppercase">Đã hoàn tiền</span>'),
                                        default => $record?->payment_status,
                                    }),
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
                                    $html .= "<p><b>Phương thức:</b> " . ($record->shipping_method_name ?: 'Giao hàng tiêu chuẩn') . "</p>";
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
                    ]),

                Forms\Components\Section::make('Ki kiện hàng cần chuẩn bị')
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
                                    $html .= "</div>";
                                    
                                    foreach ($pkgItems as $item) {
                                        $imageUrl = $item->image;
                                        if ($imageUrl) {
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable()
                    ->label('Mã đơn hàng')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('tracking_number')
                    ->searchable()
                    ->label('Mã vận đơn (GHN)')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Khách hàng')
                    ->searchable(),
                Tables\Columns\TextColumn::make('shipping_method_name')
                    ->label('Đơn vị vận chuyển')
                    ->state(function ($record) {
                        $name = $record->shipping_method_name ?: 'Giao hàng tiêu chuẩn';
                        if ($record->status === 'shipping' || $record->status === 'completed' || $record->tracking_number) {
                            return "Giao Hàng Nhanh (GHN) - " . $name;
                        }
                        return $name;
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Trạng thái giao hàng')
                    ->colors([
                        'warning' => 'packed',
                        'primary' => 'shipping',
                        'success' => 'completed',
                        'danger' => 'failed',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'packed' => 'Chờ giao hàng',
                        'shipping' => 'Đang giao hàng',
                        'completed' => 'Giao hàng thành công',
                        'failed' => 'Giao hàng thất bại',
                        default => $state
                    }),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Thanh toán')
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => ['pending_payment', 'failed'],
                        'success' => 'paid',
                        'info' => 'refunded',
                    ]),
                Tables\Columns\TextColumn::make('expected_delivery_date')
                    ->label('Dự kiến giao')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'packed' => 'Chờ giao hàng',
                        'shipping' => 'Đang giao hàng',
                        'completed' => 'Giao hàng thành công',
                        'failed' => 'Giao hàng thất bại',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogistics::route('/'),
            'edit' => Pages\EditLogistics::route('/{record}/edit'),
        ];
    }
}
