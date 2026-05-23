<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Models\Voucher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;
    protected static ?string $navigationIcon  = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Voucher';
    protected static ?string $navigationGroup = 'Khuyến mãi';
    protected static ?int    $navigationSort  = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Thông tin cơ bản')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Mã voucher')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('Mã phân biệt hoa/thường. VD: VNM12OFF')
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('name')
                        ->label('Tên voucher')
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('description')
                        ->label('Mô tả')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('banner_image')
                        ->label('Ảnh banner')
                        ->image()
                        ->directory('vouchers')
                        ->imageResizeMode('cover')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Cấu hình giảm giá')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Loại giảm')
                        ->options(['percent' => '% Phần trăm', 'fixed' => 'Số tiền cố định'])
                        ->default('percent')
                        ->required()
                        ->reactive(),

                    Forms\Components\TextInput::make('discount_value')
                        ->label(fn (Forms\Get $get) => $get('type') === 'percent' ? 'Giảm (%)' : 'Giảm (VND)')
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    Forms\Components\TextInput::make('max_discount_amount')
                        ->label('Giảm tối đa (VND)')
                        ->numeric()
                        ->visible(fn (Forms\Get $get) => $get('type') === 'percent')
                        ->helperText('Để trống = không giới hạn'),

                    Forms\Components\TextInput::make('min_order_amount')
                        ->label('Đơn hàng tối thiểu (VND)')
                        ->numeric()
                        ->default(0)
                        ->required(),
                ]),

            Forms\Components\Section::make('Điều kiện sản phẩm')
                ->schema([
                    Forms\Components\Select::make('applicable_product_ids')
                        ->label('Chỉ áp dụng cho sản phẩm')
                        ->multiple()
                        ->searchable()
                        ->options(\App\Models\Product::pluck('name', 'id'))
                        ->helperText('Để trống = áp dụng tất cả sản phẩm'),
                ]),

            Forms\Components\Section::make('Số lượng & Thời hạn')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('total_quantity')
                        ->label('Tổng số lượng phát hành')
                        ->numeric()
                        ->default(0)
                        ->helperText('0 = Không giới hạn'),

                    Forms\Components\Placeholder::make('used_count')
                        ->label('Đã sử dụng')
                        ->content(fn (?Voucher $record) => $record?->used_count ?? 0),

                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Bắt đầu')
                        ->nullable(),

                    Forms\Components\DateTimePicker::make('expires_at')
                        ->label('Kết thúc')
                        ->nullable(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Kích hoạt')
                        ->default(true)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Mã')
                    ->badge()
                    ->color('primary')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Tên voucher')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Loại')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'percent' ? '% Phần trăm' : 'Số tiền')
                    ->color(fn ($state) => $state === 'percent' ? 'success' : 'warning'),

                Tables\Columns\TextColumn::make('discount_value')
                    ->label('Giảm')
                    ->formatStateUsing(function ($state, Voucher $record) {
                        if ($record->type === 'percent') {
                            $s = "{$state}%";
                            if ($record->max_discount_amount) {
                                $s .= ' (tối đa ' . number_format($record->max_discount_amount, 0, '.', '.') . 'đ)';
                            }
                            return $s;
                        }
                        return number_format($state, 0, '.', '.') . 'đ';
                    }),

                Tables\Columns\TextColumn::make('min_order_amount')
                    ->label('Đơn tối thiểu')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, '.', '.') . 'đ'),

                Tables\Columns\TextColumn::make('usage')
                    ->label('Đã dùng / Tổng')
                    ->getStateUsing(fn (Voucher $record) =>
                        $record->used_count . ' / ' . ($record->total_quantity === 0 ? '∞' : $record->total_quantity)
                    ),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Hết hạn')
                    ->dateTime('d/m/Y')
                    ->color(fn ($state) => $state && now()->gt($state) ? 'danger' : 'success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Kích hoạt')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Loại')
                    ->options(['percent' => 'Phần trăm', 'fixed' => 'Số tiền']),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'edit'   => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}
