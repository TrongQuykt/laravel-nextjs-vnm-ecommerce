<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockReservationResource\Pages;
use App\Models\StockReservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class StockReservationResource extends Resource
{
    protected static ?string $model = StockReservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    
    protected static ?string $navigationLabel = 'Giữ kho tạm thời';
    
    protected static ?string $navigationGroup = 'Kho hàng';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin giữ kho')
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
                        Forms\Components\TextInput::make('order_number')
                            ->label('Mã đơn hàng')
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Số lượng')
                            ->numeric()
                            ->required(),
                        Forms\Components\DateTimePicker::make('reserved_at')
                            ->label('Thời gian giữ')
                            ->required(),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Thời gian hết hạn')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Trạng thái')
                            ->options([
                                'pending' => 'Chờ xử lý',
                                'confirmed' => 'Đã xác nhận',
                                'released' => 'Đã hoàn',
                                'expired' => 'Hết hạn',
                            ])
                            ->required(),
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
                TextColumn::make('order_number')
                    ->label('Mã đơn hàng')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Số lượng')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'info' => 'released',
                        'danger' => 'expired',
                    ]),
                TextColumn::make('reserved_at')
                    ->label('Thời gian giữ')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Thời gian hết hạn')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($record) => $record->expires_at->isPast() ? 'danger' : null),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Chờ xử lý',
                        'confirmed' => 'Đã xác nhận',
                        'released' => 'Đã hoàn',
                        'expired' => 'Hết hạn',
                    ]),
                SelectFilter::make('is_expired')
                    ->label('Đã hết hạn')
                    ->query(function ($query, $data) {
                        if ($data === 'yes') {
                            return $query->where('expires_at', '<', now());
                        }
                        if ($data === 'no') {
                            return $query->where('expires_at', '>', now());
                        }
                        return $query;
                    })
                    ->options([
                        'yes' => 'Đã hết hạn',
                        'no' => 'Chưa hết hạn',
                    ]),
            ])
            ->defaultSort('reserved_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListStockReservations::route('/'),
            'create' => Pages\CreateStockReservation::route('/create'),
            'edit' => Pages\EditStockReservation::route('/{record}/edit'),
        ];
    }
}
