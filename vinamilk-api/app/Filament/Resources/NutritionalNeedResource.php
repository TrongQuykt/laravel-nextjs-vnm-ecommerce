<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NutritionalNeedResource\Pages;
use App\Filament\Resources\NutritionalNeedResource\RelationManagers;
use App\Models\NutritionalNeed;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NutritionalNeedResource extends Resource
{
    protected static ?string $model = NutritionalNeed::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Thuộc tính';

    protected static ?string $modelLabel = 'Nhu cầu dinh dưỡng';

    protected static ?int $navigationSort = 5;

    protected static ?string $pluralModelLabel = 'Nhu cầu dinh dưỡng';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make("name")
                    ->label("Tên nhu cầu")
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set("slug", \Illuminate\Support\Str::slug($state))),
                Forms\Components\TextInput::make("slug")
                    ->required()
                    ->maxLength(255)
                    ->unique(NutritionalNeed::class, 'slug', ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")
                    ->searchable()
                    ->sortable()
                    ->weight("bold"),
                Tables\Columns\TextColumn::make("slug")
                    ->color("gray"),
            ])
            ->filters([
                //
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
            'index' => Pages\ManageNutritionalNeeds::route('/'),
        ];
    }
}



