<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionPageSettingResource\Pages;
use App\Filament\Resources\PromotionPageSettingResource\RelationManagers;
use App\Models\PromotionPageSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotionPageSettingResource extends Resource
{
    protected static ?string $model = PromotionPageSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $modelLabel = 'Cấu hình trang Khuyến mãi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Banner chính (Hero)')
                    ->description('Banner nằm ở trên cùng của trang Khuyến mãi, chiếm toàn bộ chiều ngang.')
                    ->schema([
                        Forms\Components\Select::make('campaign_id')
                            ->relationship('campaign', 'name')
                            ->label('Chiến dịch KM')
                            ->nullable()
                            ->searchable(),
                        Forms\Components\FileUpload::make('hero_image_path')
                            ->label('Hình ảnh Banner')
                            ->image()
                            ->disk('public')
                            ->directory('promotions/hero')
                            ->required(),
                        Forms\Components\TextInput::make('hero_title')
                            ->label('Tiêu đề chính (Không bắt buộc)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('hero_subtitle')
                            ->label('Tiêu đề phụ (Không bắt buộc)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('hero_link_url')
                            ->label('Đường dẫn khi click vào banner')
                            ->placeholder('e.g. /flash-sale-hot')
                            ->maxLength(255),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('hero_image_path'),
                Tables\Columns\TextColumn::make('hero_link_url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hero_title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hero_subtitle')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotionPageSettings::route('/'),
            'create' => Pages\CreatePromotionPageSetting::route('/create'),
            'edit' => Pages\EditPromotionPageSetting::route('/{record}/edit'),
        ];
    }
}
