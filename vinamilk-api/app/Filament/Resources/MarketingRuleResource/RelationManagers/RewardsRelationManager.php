<?php

namespace App\Filament\Resources\MarketingRuleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'rewards';
    protected static ?string $title       = 'Phần thưởng (Rewards)';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('reward_type')
                    ->label('Loại phần thưởng')
                    ->options([
                        'gift_product'        => '🎁 Tặng quà cố định (Tự động vào giỏ)',
                        'gift_product_choice' => '🎀 Tặng quà (Khách hàng tự chọn 1 trong nhiều món)',
                    ])
                    ->required()
                    ->reactive(),

                Forms\Components\TextInput::make('group_id')
                    ->label('Thuộc nhóm điều kiện')
                    ->helperText('Chỉ áp dụng khi nhóm điều kiện số này thỏa mãn')
                    ->numeric()
                    ->default(1)
                    ->required(),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Thứ tự áp dụng')
                    ->numeric()
                    ->default(1)
                    ->required(),
            ]),

            Forms\Components\Section::make('Cấu hình phần thưởng')
                ->schema([
                    // Case 1: Tặng quà cố định
                    Forms\Components\Grid::make(2)
                        ->visible(fn(Forms\Get $get) => $get('reward_type') === 'gift_product')
                        ->schema([
                            Forms\Components\Select::make('value.item_type')
                                ->label('Loại vật phẩm')
                                ->options([
                                    'product' => '📦 Sản phẩm bán',
                                    'gift'    => '🎁 Vật phẩm quà tặng',
                                ])
                                ->default('product')
                                ->required()
                                ->reactive(),

                            Forms\Components\Select::make('value.item_id')
                                ->label('Chọn món quà')
                                ->searchable()
                                ->options(fn(Forms\Get $get) => 
                                    $get('value.item_type') === 'gift'
                                        ? \App\Models\MarketingGift::where('is_active', true)->pluck('name', 'id')
                                        : \App\Models\Product::pluck('name', 'id')
                                )
                                ->required(),

                            Forms\Components\TextInput::make('value.quantity')
                                ->label('Số lượng tặng')
                                ->numeric()
                                ->default(1)
                                ->required(),

                            Forms\Components\TextInput::make('value.volume')
                                ->label('Dung tích (không bắt buộc)')
                                ->placeholder('VD: 180ml, 110ml...'),

                            Forms\Components\TextInput::make('value.packing')
                                ->label('Quy cách (không bắt buộc)')
                                ->placeholder('VD: Lốc 4, Thùng 24...'),
                        ]),

                    // Case 2: Tặng quà cho phép chọn
                    Forms\Components\Group::make()
                        ->visible(fn(Forms\Get $get) => $get('reward_type') === 'gift_product_choice')
                        ->schema([
                            Forms\Components\Repeater::make('value.items')
                                ->label('Danh sách các món quà để khách chọn')
                                ->schema([
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\Select::make('type')
                                            ->label('Loại')
                                            ->options(['product' => 'Sản phẩm', 'gift' => 'Quà tặng'])
                                            ->default('gift')
                                            ->required()
                                            ->reactive(),
                                        Forms\Components\Select::make('id')
                                            ->label('Vật phẩm')
                                            ->searchable()
                                            ->options(fn(Forms\Get $get) => 
                                                $get('type') === 'gift'
                                                    ? \App\Models\MarketingGift::where('is_active', true)->pluck('name', 'id')
                                                    : \App\Models\Product::pluck('name', 'id')
                                            )
                                            ->required(),
                                    ]),
                                    Forms\Components\Grid::make(3)->schema([
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('SL tặng')
                                            ->numeric()
                                            ->default(1)
                                            ->required(),
                                        Forms\Components\TextInput::make('volume')
                                            ->label('Dung tích')
                                            ->placeholder('180ml'),
                                        Forms\Components\TextInput::make('packing')
                                            ->label('Quy cách')
                                            ->placeholder('Lốc 4'),
                                    ]),
                                ])
                                ->columnSpanFull()
                                ->itemLabel(fn(array $state): ?string => ($state['type'] ?? '') . ' #' . ($state['id'] ?? '')),

                            Forms\Components\TextInput::make('value.pick_count')
                                ->label('Số lượng món khách được chọn trong danh sách trên')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->columnSpan(1),
                        ]),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('50px'),

                Tables\Columns\TextColumn::make('group_id')
                    ->label('Nhóm')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reward_type')
                    ->label('Loại phần thưởng')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn($state) => match($state) {
                        'gift_product'        => '🎁 Tặng SP cố định',
                        'gift_product_choice' => '🎀 Tặng (user chọn)',
                        'discount_percent'    => '📉 Giảm %',
                        'discount_amount'     => '💸 Giảm số tiền',
                        'discount_product'    => '🏷 Giảm SP',
                        'free_shipping'       => '🚚 Free Ship',
                        'cashback_points'     => '⭐ Hoàn điểm',
                        default               => $state,
                    }),

                Tables\Columns\TextColumn::make('value')
                    ->label('Cấu hình chi tiết')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state) return '-';
                        
                        return match($record->reward_type) {
                            'gift_product' => sprintf(
                                "🎁 %s #%s (SL: %s) %s %s",
                                ($state['item_type'] ?? '') === 'gift' ? 'Quà' : 'SP',
                                $state['item_id'] ?? '?',
                                $state['quantity'] ?? 1,
                                $state['volume'] ?? '',
                                $state['packing'] ?? ''
                            ),
                            'gift_product_choice' => sprintf(
                                "🎀 Chọn %s món từ danh sách (%s món có sẵn)",
                                $state['pick_count'] ?? 1,
                                count($state['items'] ?? [])
                            ),
                            default => json_encode($state)
                        };
                    })
                    ->wrap(),
            ])
            ->defaultSort('group_id', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order');
    }
}
