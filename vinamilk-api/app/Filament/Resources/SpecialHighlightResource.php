<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpecialHighlightResource\Pages;
use App\Filament\Resources\SpecialHighlightResource\RelationManagers;
use App\Models\SpecialHighlight;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpecialHighlightResource extends Resource
{
    protected static ?string $model = SpecialHighlight::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';

    protected static ?string $navigationLabel = 'Special Highlights';

    protected static ?string $modelLabel = 'Special Highlight';

    protected static ?string $pluralModelLabel = 'Special Highlights';

    protected static ?string $navigationGroup = 'Thuộc tính';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin biểu tượng')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên biểu tượng')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('icon')
                            ->label('Hình ảnh biểu tượng')
                            ->image()
                            ->disk('public')
                            ->directory('highlights')
                            ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('icon')
                    ->label('Biểu tượng')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên biểu tượng')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
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
            'index' => Pages\ListSpecialHighlights::route('/'),
            'create' => Pages\CreateSpecialHighlight::route('/create'),
            'edit' => Pages\EditSpecialHighlight::route('/{record}/edit'),
        ];
    }
}
