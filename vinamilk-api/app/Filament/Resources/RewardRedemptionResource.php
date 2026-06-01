<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RewardRedemptionResource\Pages;
use App\Filament\Resources\RewardRedemptionResource\RelationManagers;
use App\Models\RewardRedemption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RewardRedemptionResource extends Resource
{
    protected static ?string $model = RewardRedemption::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Khuyến mãi';

    protected static ?string $modelLabel = 'Lịch sử Quy đổi quà';

    protected static ?int $navigationSort = 6;

    protected static ?string $pluralModelLabel = 'Lịch sử Quy đổi quà';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('reward_id')
                    ->relationship('reward', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('points_spent')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('reward.image')
                    ->label('Hình ảnh')
                    ->circular(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reward.name')
                    ->label('Reward')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('points_spent')
                    ->label('Points Spent')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Redeemed At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListRewardRedemptions::route('/'),
            'create' => Pages\CreateRewardRedemption::route('/create'),
            'edit' => Pages\EditRewardRedemption::route('/{record}/edit'),
        ];
    }
}
