<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RolePermissionResource\Pages;
use App\Services\ActivityLogger;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Phân quyền Admin';
    protected static ?string $modelLabel = 'Phân quyền';
    protected static ?string $pluralModelLabel = 'Phân quyền Admin';
    protected static ?string $navigationGroup = 'Tài khoản';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin vai trò')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên vai trò')
                            ->required()
                            ->unique(ignoreRecord: true),
                    ]),
                Forms\Components\Section::make('Phân quyền')
                    ->description('Chọn quyền cho vai trò này')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('Quyền hạn')
                            ->relationship(
                                name: 'permissions',
                                titleAttribute: 'name',
                            )
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Vai trò')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Số quyền')
                    ->counts('permissions')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Số user')
                    ->counts('users')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function ($record) {
                        ActivityLogger::logUpdate('Phân quyền Admin', $record->id, $record->getOriginal(), $record->toArray());
                    }),
                Tables\Actions\DeleteAction::make()
                    ->after(function ($record) {
                        ActivityLogger::logDelete('Phân quyền Admin', $record->id, $record->toArray());
                    }),
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
            'index' => Pages\ListRolePermissions::route('/'),
            'create' => Pages\CreateRolePermission::route('/create'),
            'edit' => Pages\EditRolePermission::route('/{record}/edit'),
        ];
    }
}
