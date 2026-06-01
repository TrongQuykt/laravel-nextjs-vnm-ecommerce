<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionFlashSaleResource\Pages;
use App\Models\Product;
use App\Models\PromotionFlashSale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionFlashSaleResource extends Resource
{
    protected static ?string $model = PromotionFlashSale::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationGroup = 'Khuyến mãi';
    protected static ?string $modelLabel = 'Flash Sale';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Thông tin Flash Sale')
                ->schema([
                    Forms\Components\Select::make('campaign_id')
                        ->relationship('campaign', 'name')
                        ->label('Chiến dịch KM')
                        ->nullable()
                        ->searchable()
                        ->live(),
                    Forms\Components\TextInput::make('title')
                        ->label('Tiêu đề chính')
                        ->placeholder('e.g. Flash sales tuần lễ Vinamilk')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('content')
                        ->label('Nội dung mô tả')
                        ->placeholder('e.g. Đừng bỏ lỡ các đợt sóng Flash Sales cực hot...')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\DateTimePicker::make('start_time')
                        ->label('Thời gian bắt đầu')
                        ->helperText('Nếu thuộc một Chiến dịch, thời gian của Chiến dịch sẽ được ưu tiên áp dụng (từ 00:00 ngày bắt đầu đến 23:59 ngày kết thúc).')
                        ->required(fn(Forms\Get $get) => empty($get('campaign_id'))),
                    Forms\Components\DateTimePicker::make('end_time')
                        ->label('Thời gian kết thúc')
                        ->required(fn(Forms\Get $get) => empty($get('campaign_id'))),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Kích hoạt')
                        ->default(true),
                ])->columns(2),

            Forms\Components\Section::make('Sản phẩm Flash Sale')
                ->description('Chọn thủ công các sản phẩm sẽ hiển thị trong đợt Flash Sale này.')
                ->schema([
                    Forms\Components\Select::make('products')
                        ->label('Danh sách sản phẩm')
                        ->multiple()
                        ->relationship('products', 'name')
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(function (Product $record) {
                            $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($record->main_image);
                            return "<div style='display:flex; align-items:center; gap:0.5rem;'>
                                        <img src='{$imageUrl}' style='width: 32px; height: 32px; object-fit: cover; border-radius: 4px;' />
                                        <span>{$record->name}</span>
                                    </div>";
                        })
                        ->allowHtml()
                        ->helperText('Tìm kiếm và chọn nhiều sản phẩm. Thứ tự hiển thị theo thứ tự bạn chọn.')
                        ->columnSpanFull(),
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Số sản phẩm')
                    ->counts('products')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Bắt đầu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Kết thúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Kích hoạt')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotionFlashSales::route('/'),
            'create' => Pages\CreatePromotionFlashSale::route('/create'),
            'edit' => Pages\EditPromotionFlashSale::route('/{record}/edit'),
        ];
    }
}
