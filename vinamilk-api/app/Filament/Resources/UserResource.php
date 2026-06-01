<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Filament\Traits\HasRolePermissions;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    use HasRolePermissions;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Khách hàng';
    protected static ?string $modelLabel = 'Khách hàng';
    protected static ?string $pluralModelLabel = 'Khách hàng';
    protected static ?string $navigationGroup = 'Tài khoản';

    protected static ?int $navigationSort = 2;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->name;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['tenant'])
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['Super Admin', 'Administrator', 'Shop Manager', 'Order Processor', 'Content Editor']);
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin cá nhân')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Họ và tên')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Số điện thoại')
                            ->tel()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('reward_points')
                            ->label('Điểm thành viên')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Bảo mật')
                    ->description('Cập nhật mật khẩu mới cho khách hàng. Nếu để trống sẽ giữ nguyên mật khẩu cũ.')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Mật khẩu mới')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Họ tên')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->email ?? ''),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Số điện thoại')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reward_points')
                    ->label('Điểm thành viên')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => number_format($state)),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Số đơn hàng')
                    ->counts('orders')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Đã xác nhận')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->getStateUsing(fn ($record) => !is_null($record->email_verified_at)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày đăng ký')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(\App\Filament\Exports\UserExporter::class),
            ])
            ->filters([
                Tables\Filters\Filter::make('verified')
                    ->label('Đã xác nhận email')
                    ->query(fn ($query) => $query->whereNotNull('email_verified_at')),
                Tables\Filters\Filter::make('unverified')
                    ->label('Chưa xác nhận email')
                    ->query(fn ($query) => $query->whereNull('email_verified_at')),
                Tables\Filters\Filter::make('has_orders')
                    ->label('Có đơn hàng')
                    ->query(fn ($query) => $query->has('orders')),
                Tables\Filters\Filter::make('no_orders')
                    ->label('Chưa có đơn hàng')
                    ->query(fn ($query) => $query->doesntHave('orders')),
                Tables\Filters\Filter::make('created_at')
                    ->label('Ngày đăng ký')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Từ ngày'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Đến ngày'),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('add_points')
                    ->label('Cộng điểm')
                    ->icon('heroicon-o-gift')
                    ->form([
                        Forms\Components\TextInput::make('points')
                            ->label('Số điểm')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\Textarea::make('reason')
                            ->label('Lý do')
                            ->required(),
                    ])
                    ->action(function ($record, $data) {
                        $record->increment('reward_points', $data['points']);
                        \Filament\Notifications\Notification::make()
                            ->title('Đã cộng điểm thành công')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('verify_email')
                        ->label('Xác nhận email')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['email_verified_at' => now()]);
                            }
                        }),
                    Tables\Actions\BulkAction::make('add_points_bulk')
                        ->label('Cộng điểm hàng loạt')
                        ->icon('heroicon-o-gift')
                        ->form([
                            Forms\Components\TextInput::make('points')
                                ->label('Số điểm')
                                ->required()
                                ->numeric()
                                ->minValue(1),
                            Forms\Components\Textarea::make('reason')
                                ->label('Lý do')
                                ->required(),
                        ])
                        ->action(function ($records, $data) {
                            foreach ($records as $record) {
                                $record->increment('reward_points', $data['points']);
                            }
                        }),
                ]),
            ])
            ->paginated([25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AddressesRelationManager::class,
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\RewardRedemptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
