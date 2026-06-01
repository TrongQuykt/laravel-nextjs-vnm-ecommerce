<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Filament\Resources\ActivityLogResource\RelationManagers;
use App\Filament\Traits\HasRolePermissions;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityLogResource extends Resource
{
    use HasRolePermissions;
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Hệ thống';

    protected static ?string $navigationLabel = 'Nhật ký hoạt động';

    protected static ?string $modelLabel = 'Nhật ký';

    protected static ?string $pluralModelLabel = 'Nhật ký hoạt động';

    protected static ?int $navigationSort = 5;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin hoạt động')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label('Người dùng')
                                    ->disabled(),
                                Forms\Components\Select::make('action')
                                    ->label('Hành động')
                                    ->options([
                                        'create' => 'Tạo mới',
                                        'update' => 'Cập nhật',
                                        'delete' => 'Xóa',
                                        'view' => 'Xem',
                                        'login' => 'Đăng nhập',
                                        'logout' => 'Đăng xuất',
                                        'export' => 'Xuất dữ liệu',
                                        'import' => 'Nhập dữ liệu',
                                    ])
                                    ->disabled(),
                                Forms\Components\TextInput::make('resource_type')
                                    ->label('Loại tài nguyên')
                                    ->disabled(),
                                Forms\Components\TextInput::make('resource_id')
                                    ->label('ID tài nguyên')
                                    ->numeric()
                                    ->disabled(),
                            ]),
                        Forms\Components\Textarea::make('description')
                            ->label('Mô tả')
                            ->columnSpanFull()
                            ->disabled(),
                    ]),

                Forms\Components\Section::make('Thông tin thay đổi')
                    ->schema([
                        Forms\Components\KeyValue::make('old_values')
                            ->label('Giá trị cũ')
                            ->keyLabel('Trường')
                            ->valueLabel('Giá trị')
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('new_values')
                            ->label('Giá trị mới')
                            ->keyLabel('Trường')
                            ->valueLabel('Giá trị')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record && ($record->old_values || $record->new_values)),

                Forms\Components\Section::make('Thông tin hệ thống')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('ip_address')
                                    ->label('Địa chỉ IP')
                                    ->disabled(),
                                Forms\Components\TextInput::make('url')
                                    ->label('URL')
                                    ->disabled(),
                            ]),
                        Forms\Components\Textarea::make('user_agent')
                            ->label('User Agent')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Thời gian')
                            ->disabled()
                            ->seconds(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Người dùng')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('action')
                    ->label('Hành động')
                    ->colors([
                        'create' => 'success',
                        'update' => 'warning',
                        'delete' => 'danger',
                        'view' => 'info',
                        'login' => 'success',
                        'logout' => 'gray',
                        'export' => 'info',
                        'import' => 'info',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'create' => 'Tạo mới',
                        'update' => 'Cập nhật',
                        'delete' => 'Xóa',
                        'view' => 'Xem',
                        'login' => 'Đăng nhập',
                        'logout' => 'Đăng xuất',
                        'export' => 'Xuất dữ liệu',
                        'import' => 'Nhập dữ liệu',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('resource_type')
                    ->label('Loại tài nguyên')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Mô tả')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->label('Người dùng'),
                Tables\Filters\SelectFilter::make('action')
                    ->label('Hành động')
                    ->options([
                        'create' => 'Tạo mới',
                        'update' => 'Cập nhật',
                        'delete' => 'Xóa',
                        'view' => 'Xem',
                        'login' => 'Đăng nhập',
                        'logout' => 'Đăng xuất',
                        'export' => 'Xuất dữ liệu',
                        'import' => 'Nhập dữ liệu',
                    ]),
                Tables\Filters\SelectFilter::make('resource_type')
                    ->label('Loại tài nguyên')
                    ->options([
                        'Product' => 'Sản phẩm',
                        'Order' => 'Đơn hàng',
                        'User' => 'Người dùng',
                        'Category' => 'Danh mục',
                        'Brand' => 'Thương hiệu',
                        'Voucher' => 'Voucher',
                        'Promotion' => 'Khuyến mãi',
                        'Reward' => 'Phần thưởng',
                        'BlogPost' => 'Bài viết',
                        'Banner' => 'Banner',
                        'Store' => 'Cửa hàng',
                    ]),
                Tables\Filters\Filter::make('date')
                    ->label('Ngày')
                    ->form(fn () => [
                        Forms\Components\DatePicker::make('from')
                            ->label('Từ'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Đến'),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['from'] ?? null) {
                            $query->where('created_at', '>=', $data['from']);
                        }
                        if ($data['until'] ?? null) {
                            $query->where('created_at', '<=', $data['until']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->paginated([25, 50, 100]);
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
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }
}
