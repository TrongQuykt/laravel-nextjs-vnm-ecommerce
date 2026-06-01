<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CareGreetingCardResource\Pages;
use App\Models\CareGreetingCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CareGreetingCardResource extends Resource
{
    protected static ?string $model = CareGreetingCard::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Chăm sóc khách hàng';
    protected static ?string $navigationLabel = 'Thiệp chúc';
    protected static ?string $modelLabel = 'Thiệp';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Tên mẫu')->required(),
            Forms\Components\Textarea::make('message')->label('Lời nhắn')->rows(6)->required()->columnSpanFull(),
            Forms\Components\FileUpload::make('preview_image_path')->label('Ảnh preview')->image()->disk('public')->directory('care/cards'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\ImageColumn::make('preview_image_path')->disk('public')->height(40),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCareGreetingCards::route('/'),
            'create' => Pages\CreateCareGreetingCard::route('/create'),
            'edit'   => Pages\EditCareGreetingCard::route('/{record}/edit'),
        ];
    }
}
