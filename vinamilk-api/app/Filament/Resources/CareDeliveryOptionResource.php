<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CareDeliveryOptionResource\Pages;
use App\Models\CareDeliveryOption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CareDeliveryOptionResource extends Resource
{
    protected static ?string $model = CareDeliveryOption::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Chăm sóc khách hàng';
    protected static ?string $navigationLabel = 'Số lần giao hàng';
    protected static ?string $modelLabel = 'Số lần giao hàng';
    protected static ?string $pluralModelLabel = 'Số lần giao hàng';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->description('Chỉ cấu hình số lần giao trong gói (3 / 6 / 9 tháng). Tổng tiền = giá Care mỗi kỳ × số lần giao. Không có % giảm thêm ở đây — giá ưu đãi nằm ở giá sản phẩm Care.')
                ->schema([
                    Forms\Components\TextInput::make('delivery_count')
                        ->label('Số lần giao hàng')
                        ->numeric()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('Ví dụ: 3 = giao 3 tháng liên tiếp, mỗi tháng 1 lần.'),
                    Forms\Components\TextInput::make('sort_order')->label('Thứ tự hiển thị')->numeric()->default(0),
                    Forms\Components\Toggle::make('is_active')->label('Đang dùng')->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('delivery_count')->label('Số lần giao')->suffix(' lần')->weight('bold'),
            Tables\Columns\IconColumn::make('is_active')->label('Đang dùng')->boolean(),
            Tables\Columns\TextColumn::make('sort_order')->label('Thứ tự'),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCareDeliveryOptions::route('/'),
            'create' => Pages\CreateCareDeliveryOption::route('/create'),
            'edit'   => Pages\EditCareDeliveryOption::route('/{record}/edit'),
        ];
    }
}
