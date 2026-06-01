<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockAlertResource\Pages;
use App\Filament\Resources\StockAlertResource\RelationManagers;
use App\Models\StockAlert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockAlertResource extends Resource
{
    protected static ?string $model = StockAlert::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationGroup = 'Kho hàng';
    protected static ?string $navigationLabel = 'Cảnh báo tồn kho';
    protected static ?string $modelLabel = 'Cảnh báo tồn kho';
    protected static ?string $pluralModelLabel = 'Cảnh báo tồn kho';
    protected static ?int $navigationSort = 3;
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_variant_id')
                    ->relationship('productVariant', 'name')
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?? 'Variant #' . $record->id)
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'low_stock' => 'Sắp hết hàng',
                        'out_of_stock' => 'Hết hàng',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('current_quantity')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('threshold')
                    ->numeric()
                    ->required(),
                Forms\Components\Toggle::make('is_resolved')
                    ->label('Đã giải quyết'),
                Forms\Components\DateTimePicker::make('resolved_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['productVariant.product', 'productVariant.volume', 'productVariant.packagingType', 'productVariant.flavor']))
            ->columns([
                Tables\Columns\TextColumn::make('productVariant.product.name')
                    ->label('Sản phẩm')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('variant_display_name')
                    ->label('Variant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Loại cảnh báo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low_stock' => 'warning',
                        'out_of_stock' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low_stock' => 'Sắp hết hàng',
                        'out_of_stock' => 'Hết hàng',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('current_quantity')
                    ->label('Số lượng hiện tại')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('threshold')
                    ->label('Ngưỡng')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_resolved')
                    ->label('Đã giải quyết')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Thời gian tạo')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'low_stock' => 'Sắp hết hàng',
                        'out_of_stock' => 'Hết hàng',
                    ]),
                Tables\Filters\Filter::make('unresolved')
                    ->label('Chưa giải quyết')
                    ->query(fn (Builder $query): Builder => $query->where('is_resolved', false)),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_resolved')
                    ->label('Đánh dấu đã giải quyết')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (StockAlert $record) {
                        $record->markAsResolved();
                    })
                    ->visible(fn (StockAlert $record): bool => !$record->is_resolved),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockAlerts::route('/'),
            'create' => Pages\CreateStockAlert::route('/create'),
            'edit' => Pages\EditStockAlert::route('/{record}/edit'),
        ];
    }
}
