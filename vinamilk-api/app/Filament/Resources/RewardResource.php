<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RewardResource\Pages;
use App\Filament\Resources\RewardResource\RelationManagers;
use App\Filament\Traits\HasRolePermissions;
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
    use HasRolePermissions;
    protected static ?string $model = Reward::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Khuyến mãi';
    protected static ?string $modelLabel = 'Phần thưởng';
    protected static ?string $pluralModelLabel = 'Quà tặng & Voucher';

    protected static ?int $navigationSort = 5;

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
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state && !$set('code')) {
                                    $prefix = $state === 'gift' ? 'GF-' : 'VC-';
                                    $randomPart = strtoupper(substr(md5(uniqid()), 0, 8));
                                    $set('code', $prefix . $randomPart);
                                }
                            }),
                        Forms\Components\TextInput::make('code')
                            ->label('Mã phần thưởng')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->helperText('Mã sẽ tự động tạo dựa trên loại phần thưởng'),
                        Forms\Components\RichEditor::make('description')
                            ->label('Mô tả chi tiết')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'link',
                                'blockquote',
                                'h2',
                                'h3',
                            ]),
                        Forms\Components\FileUpload::make('image')
                            ->label('Hình ảnh')
                            ->image()
                            ->acceptedFileTypes(['image/*', 'image/webp'])
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
