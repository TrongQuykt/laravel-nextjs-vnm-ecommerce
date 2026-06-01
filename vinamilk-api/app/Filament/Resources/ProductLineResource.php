<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductLineResource\Pages;
use App\Filament\Resources\ProductLineResource\RelationManagers;
use App\Models\ProductLine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductLineResource extends Resource
{
    protected static ?string $model = ProductLine::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Sản phẩm';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('brand_id')
                    ->label('Thương hiệu')
                    ->relationship('brand', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('category_id', null)),
                Forms\Components\Select::make('category_id')
                    ->label('Thuộc Danh mục')
                    ->options(function (Forms\Get $get) {
                        $brandId = $get('brand_id');
                        if (!$brandId) return [];
                        return \App\Models\Category::where('brand_id', $brandId)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Forms\Get $get) => !$get('brand_id'))
                    ->helperText(fn (Forms\Get $get) => !$get('brand_id') ? 'Chọn Thương hiệu trước' : null),
                Forms\Components\TextInput::make("name")
                    ->label('Tên dòng sản phẩm')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set("slug", \Illuminate\Support\Str::slug($state))),
                Forms\Components\TextInput::make("slug")
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("brand.name")
                    ->label('Thương hiệu')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make("category.name")
                    ->label('Danh mục')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make("name")
                    ->label('Dòng sản phẩm')
                    ->searchable()
                    ->sortable()
                    ->weight("bold"),
                Tables\Columns\TextColumn::make("slug")
                    ->color("gray"),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->label('Thương hiệu'),
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Danh mục'),
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
            'index' => Pages\ManageProductLines::route('/'),
        ];
    }
}



