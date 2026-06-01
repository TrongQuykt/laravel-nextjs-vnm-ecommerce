<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarePageSettingResource\Pages;
use App\Models\CarePageSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class CarePageSettingResource extends Resource
{
    protected static ?string $model = CarePageSetting::class;
    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationGroup = 'Chăm sóc khách hàng';
    protected static ?string $navigationLabel = 'Nội dung trang Care';
    protected static ?string $modelLabel = 'Trang Care';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('tagline')->label('Slogan')->columnSpanFull(),
            Forms\Components\Textarea::make('intro_text')->label('Mô tả')->rows(4)->columnSpanFull(),
            Forms\Components\FileUpload::make('hero_image_path')->label('Ảnh hero')->image()->disk('public')->directory('care/hero'),
            Forms\Components\Toggle::make('premium_coming_soon')->label('Gói Cao Cấp: Sắp ra mắt')->default(true),
            Forms\Components\Repeater::make('benefits')->label('3 lợi ích')
                ->schema([
                    Forms\Components\TextInput::make('title')->required(),
                    Forms\Components\Textarea::make('description')->rows(2),
                ])->columns(1)->defaultItems(3),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCarePage::route('/')];
    }

    public static function canCreate(): bool { return false; }
}
