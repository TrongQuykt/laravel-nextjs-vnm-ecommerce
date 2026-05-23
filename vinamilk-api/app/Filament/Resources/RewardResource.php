<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RewardResource\Pages;
use App\Filament\Resources\RewardResource\RelationManagers;
use App\Models\Reward;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RewardResource extends Resource
{
    protected static ?string $model = Reward::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Cấu hình Chatbot';
    protected static ?string $modelLabel = 'Phần thưởng';
    protected static ?string $pluralModelLabel = 'Quà tặng & Voucher';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin phần thưởng')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên phần thưởng')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->label('Loại')
                            ->options([
                                'voucher' => 'Voucher giảm giá',
                                'gift' => 'Quà tặng hiện vật',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Mô tả chi tiết')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image')
                            ->label('Hình ảnh')
                            ->image()
                            ->directory('rewards'),
                    ])->columns(2),

                Forms\Components\Section::make('Cấu hình đổi thưởng')
                    ->schema([
                        Forms\Components\TextInput::make('points_required')
                            ->label('Số điểm cần đổi')
                            ->numeric()
                            ->required()
                            ->prefix('Points'),
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Số lượng còn lại')
                            ->numeric()
                            ->required()
                            ->default(0),
                        Forms\Components\TextInput::make('user_limit')
                            ->label('Giới hạn mỗi khách hàng')
                            ->numeric()
                            ->required()
                            ->default(1),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Đang hoạt động')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Ảnh'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên quà tặng')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Loại')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'voucher' => 'success',
                        'gift' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('points_required')
                    ->label('Điểm đổi')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state) . ' Điểm'),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Kho')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Kích hoạt'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRewards::route('/'),
            'create' => Pages\CreateReward::route('/create'),
            'edit' => Pages\EditReward::route('/{record}/edit'),
        ];
    }
}
