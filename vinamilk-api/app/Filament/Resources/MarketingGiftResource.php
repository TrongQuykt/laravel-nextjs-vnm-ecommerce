<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingGiftResource\Pages;
use App\Models\MarketingGift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MarketingGiftResource extends Resource
{
    protected static ?string $model = MarketingGift::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $modelLabel = 'Vật phẩm quà tặng';
    protected static ?string $pluralModelLabel = 'Kho Quà tặng';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin quà tặng')
                    ->description('Quản lý các vật phẩm quà tặng không bán (Gấu bông, túi tote, ly sứ...)')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên quà tặng')
                            ->placeholder('VD: Gấu bông size lớn')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\FileUpload::make('image')
                            ->label('Hình ảnh quà tặng')
                            ->image()
                            ->directory('marketing-gifts')
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Kích hoạt')
                            ->default(true)
                            ->inline(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Ảnh')
                    ->square(),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên vật phẩm')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Trạng thái'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
            'index' => Pages\ListMarketingGifts::route('/'),
            'create' => Pages\CreateMarketingGift::route('/create'),
            'edit' => Pages\EditMarketingGift::route('/{record}/edit'),
        ];
    }
}
