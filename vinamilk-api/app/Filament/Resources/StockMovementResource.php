<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\StockMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    
    protected static ?string $navigationLabel = 'Lịch sử tồn kho';
    
    protected static ?string $navigationGroup = 'Kho hàng';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin movement')
                    ->schema([
                        Forms\Components\Select::make('product_variant_id')
                            ->label('Biến thể sản phẩm')
                            ->relationship('productVariant', 'name')
                            ->searchable()
                            ->preload()
                            ->allowHtml()
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                $imageUrl = $record->main_image ?? $record->product->main_image ?? '';
                                $flavorName = $record->flavor?->name ?? '';
                                $volumeName = $record->volume?->name ?? '';
                                
                                return view('filament.components.product-variant-select-option', [
                                    'image' => $imageUrl,
                                    'product_name' => $record->product->name,
                                    'variant_name' => $record->name,
                                    'flavor' => $flavorName,
                                    'volume' => $volumeName,
                                ])->render();
                            })
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Số lượng')
                            ->numeric()
                            ->required()
                            ->helperText('Dương cho nhập kho, Âm cho xuất kho'),
                        Forms\Components\Select::make('type')
                            ->label('Loại movement')
                            ->options([
                                'import' => 'Nhập kho',
                                'export' => 'Xuất kho',
                                'reservation' => 'Giữ kho',
                                'release' => 'Hoàn kho',
                                'adjustment' => 'Điều chỉnh',
                                'return' => 'Trả hàng',
                                'damage' => 'Hư hỏng',
                                'transfer' => 'Chuyển kho',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('reference_type')
                            ->label('Loại tham chiếu')
                            ->helperText('Ví dụ: order, purchase_order, adjustment'),
                        Forms\Components\TextInput::make('reference_id')
                            ->label('ID tham chiếu'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Ghi chú')
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('productVariant.product.name')
                    ->label('Sản phẩm')
                    ->searchable(),
                TextColumn::make('productVariant.name')
                    ->label('Biến thể')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Số lượng')
                    ->sortable()
                    ->color(fn ($record) => $record->quantity > 0 ? 'success' : 'danger'),
                BadgeColumn::make('type')
                    ->label('Loại')
                    ->colors([
                        'success' => 'import',
                        'danger' => 'export',
                        'warning' => 'reservation',
                        'info' => 'release',
                        'primary' => 'adjustment',
                        'secondary' => 'return',
                        'danger' => 'damage',
                        'gray' => 'transfer',
                    ]),
                TextColumn::make('reference_type')
                    ->label('Loại tham chiếu')
                    ->searchable(),
                TextColumn::make('reference_id')
                    ->label('ID tham chiếu')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Người thực hiện')
                    ->searchable(),
                TextColumn::make('warehouse.name')
                    ->label('Kho')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Loại movement')
                    ->options([
                        'import' => 'Nhập kho',
                        'export' => 'Xuất kho',
                        'reservation' => 'Giữ kho',
                        'release' => 'Hoàn kho',
                        'adjustment' => 'Điều chỉnh',
                        'return' => 'Trả hàng',
                        'damage' => 'Hư hỏng',
                        'transfer' => 'Chuyển kho',
                    ]),
                SelectFilter::make('reference_type')
                    ->label('Loại tham chiếu')
                    ->options([
                        'order' => 'Đơn hàng',
                        'purchase_order' => 'Đơn nhập',
                        'adjustment' => 'Điều chỉnh',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
            'create' => Pages\CreateStockMovement::route('/create'),
            'view' => Pages\ViewStockMovement::route('/{record}'),
        ];
    }
}
