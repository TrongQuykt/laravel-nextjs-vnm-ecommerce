<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackingResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PackingResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Bán hàng';

    protected static ?string $navigationLabel = 'Đóng gói Đơn hàng';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Đơn đóng gói';

    protected static ?string $pluralModelLabel = 'Đóng gói Đơn hàng';

    public static function getEloquentQuery(): Builder
    {
        // Only show orders that are currently in 'processing' status (Chờ xử lý đóng gói)
        return parent::getEloquentQuery()->where('status', 'processing');
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

                        Forms\Components\Section::make('Thông tin Đóng gói')
                            ->schema([
                                Forms\Components\TextInput::make('order_number')
                                    ->readOnly()
                                    ->label('Mã đơn hàng'),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'processing' => 'Chờ xử lý đóng gói',
                                        'packed' => 'Đã đóng gói',
                                    ])
                                    ->required()
                                    ->label('Trạng thái đóng gói'),
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->disabled()
                                    ->label('Khách hàng'),
                            ])->columnSpan(2),

                        Forms\Components\Section::make('Tổng quan')
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Ngày đặt hàng')
                                    ->content(fn ($record) => $record?->created_at?->format('d/m/Y H:i') ?? '-'),
                                Forms\Components\Placeholder::make('delivery_type_display')
                                    ->label('Hình thức nhận hàng')
                                    ->content(fn ($record) => $record?->delivery_type === 'shipping' ? 'Giao tận nơi' : 'Nhận tại cửa hàng'),
                            ])->columnSpan(1),
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Khách hàng')
                    ->searchable(),
                Tables\Columns\TextColumn::make('delivery_type')
                    ->label('Hình thức nhận')
                    ->formatStateUsing(fn ($state) => $state === 'shipping' ? 'Giao tận nơi' : 'Tại quầy'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Trạng thái đóng gói')
                    ->colors([
                        'warning' => 'processing',
                        'success' => 'packed',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'processing' => 'Chờ xử lý đóng gói',
                        'packed' => 'Đã đóng gói',
                        default => $state
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Ngày đặt hàng')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackings::route('/'),
            'edit' => Pages\EditPacking::route('/{record}/edit'),
        ];
    }
}
