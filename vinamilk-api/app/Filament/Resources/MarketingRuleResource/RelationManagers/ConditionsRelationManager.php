<?php

namespace App\Filament\Resources\MarketingRuleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ConditionsRelationManager extends RelationManager
{
    protected static string $relationship = 'conditions';
    protected static ?string $title       = 'Điều kiện (Conditions)';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(4)->schema([

                Forms\Components\TextInput::make('group_id')
                    ->label('Nhóm #')
                    ->helperText('Cùng group_id = cùng nhóm AND/OR')
                    ->numeric()
                    ->default(1)
                    ->required(),

                Forms\Components\Select::make('group_logic')
                    ->label('Logic trong nhóm')
                    ->options(['AND' => 'AND', 'OR' => 'OR'])
                    ->default('AND')
                    ->required(),

                Forms\Components\Select::make('condition_type')
                    ->label('Loại điều kiện')
                    ->options([
                        'cart_total'        => '💰 Tổng giỏ hàng',
                        'cart_quantity'     => '📦 Tổng số lượng',
                        'product_in_cart'   => '🛍 Có sản phẩm X',
                        'product_quantity'  => '🔢 SL sản phẩm X (Hộp/Chai)',
                        'product_quantity_in_cases' => '📦 SL sản phẩm X (Tính theo THÙNG)',
                        'category_in_cart'  => '📂 Có danh mục Y',
                        'category_quantity' => '📊 SL danh mục Y',
                        'category_subtotal' => '💵 Tiền danh mục Y',
                    ])
                    ->required()
                    ->reactive()
                    ->columnSpan(1),

                Forms\Components\Select::make('operator')
                    ->label('Toán tử')
                    ->options([
                        '>='      => '>= (lớn hơn hoặc bằng)',
                        '>'       => '> (lớn hơn)',
                        '<='      => '<= (nhỏ hơn hoặc bằng)',
                        '<'       => '< (nhỏ hơn)',
                        '='       => '= (bằng)',
                        '!='      => '!= (khác)',
                        'in'      => 'in (có trong giỏ)',
                        'not_in'  => 'not_in (không có trong giỏ)',
                        'between' => 'between (trong khoảng)',
                    ])
                    ->required(),
            ]),

            Forms\Components\Section::make('Cấu hình chi tiết')
                ->schema([
                    // 1. Tổng giỏ hàng
                    Forms\Components\TextInput::make('value.amount')
                        ->label('Số tiền (VND)')
                        ->numeric()
                        ->visible(fn(Forms\Get $get) => $get('condition_type') === 'cart_total')
                        ->required(),

                    // 2. Tổng số lượng
                    Forms\Components\TextInput::make('value.quantity')
                        ->label('Tổng số lượng sản phẩm')
                        ->numeric()
                        ->visible(fn(Forms\Get $get) => $get('condition_type') === 'cart_quantity')
                        ->required(),

                    // 3. Có sản phẩm X (Sử dụng MultiSelect)
                    Forms\Components\Select::make('value.product_ids')
                        ->label('Danh sách sản phẩm')
                        ->multiple()
                        ->options(\App\Models\Product::pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->visible(fn(Forms\Get $get) => $get('condition_type') === 'product_in_cart')
                        ->required(),

                    // 4. SL sản phẩm X (Hộp/Chai)
                    Forms\Components\Grid::make(2)
                        ->visible(fn(Forms\Get $get) => $get('condition_type') === 'product_quantity')
                        ->schema([
                            Forms\Components\Select::make('value.product_id')
                                ->label('Sản phẩm')
                                ->options(\App\Models\Product::pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                            Forms\Components\TextInput::make('value.quantity')
                                ->label('Số lượng (lẻ)')
                                ->numeric()
                                ->required(),
                        ]),

                    // 5. SL sản phẩm X (Theo Thùng)
                    Forms\Components\Grid::make(2)
                        ->visible(fn(Forms\Get $get) => $get('condition_type') === 'product_quantity_in_cases')
                        ->schema([
                            Forms\Components\Select::make('value.product_id')
                                ->label('Sản phẩm')
                                ->options(\App\Models\Product::pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                            Forms\Components\TextInput::make('value.cases')
                                ->label('Số lượng (Thùng)')
                                ->numeric()
                                ->step(0.1)
                                ->required(),
                        ]),

                    // 6. Danh mục
                    Forms\Components\Select::make('value.category_id')
                        ->label('Danh mục')
                        ->options(\App\Models\Category::pluck('name', 'id'))
                        ->searchable()
                        ->visible(fn(Forms\Get $get) => in_array($get('condition_type'), ['category_in_cart', 'category_quantity', 'category_subtotal']))
                        ->required(),

                    Forms\Components\TextInput::make('value.quantity')
                        ->label('Số lượng')
                        ->numeric()
                        ->visible(fn(Forms\Get $get) => $get('condition_type') === 'category_quantity')
                        ->required(),

                    Forms\Components\TextInput::make('value.amount')
                        ->label('Số tiền (VND)')
                        ->numeric()
                        ->visible(fn(Forms\Get $get) => $get('condition_type') === 'category_subtotal')
                        ->required(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('group_id', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('group_id')
                    ->label('Nhóm')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('group_logic')
                    ->label('Logic')
                    ->badge()
                    ->color(fn($state) => $state === 'AND' ? 'primary' : 'warning'),

                Tables\Columns\TextColumn::make('condition_type')
                    ->label('Loại')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'cart_total'        => '💰 Tổng giỏ hàng',
                        'cart_quantity'     => '📦 Tổng SL',
                        'product_in_cart'   => '🛍 Sản phẩm X',
                        'product_quantity'  => '🔢 SL SP X',
                        'product_quantity_in_cases' => '📦 SL Thùng X',
                        'category_in_cart'  => '📂 Danh mục Y',
                        'category_quantity' => '📊 SL DM Y',
                        'category_subtotal' => '💵 Tiền DM Y',
                        default             => $state,
                    }),

                Tables\Columns\TextColumn::make('operator')
                    ->label('Toán tử')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('value')
                    ->label('Giá trị')
                    ->formatStateUsing(fn($state) => is_array($state)
                        ? collect($state)->map(function($v, $k) {
                            $displayValue = is_array($v) ? json_encode($v) : $v;
                            return "$k: $displayValue";
                        })->implode(', ')
                        : json_encode($state)
                    )
                    ->wrap(),
            ])
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
            ]);
    }
}
