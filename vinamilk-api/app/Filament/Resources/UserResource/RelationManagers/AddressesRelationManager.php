<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('last_name')->label('Họ')->required(),
                Forms\Components\TextInput::make('first_name')->label('Tên')->required(),
                Forms\Components\TextInput::make('phone')->label('SĐT')->required(),
                Forms\Components\TextInput::make('detail')->label('Địa chỉ chi tiết')->required(),
                Forms\Components\TextInput::make('ward')->label('Phường/Xã')->required(),
                Forms\Components\TextInput::make('district')->label('Quận/Huyện')->required(),
                Forms\Components\TextInput::make('city')->label('Tỉnh/TP')->required(),
                Forms\Components\Toggle::make('is_default')->label('Địa chỉ mặc định'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('last_name')->label('Họ'),
                Tables\Columns\TextColumn::make('first_name')->label('Tên')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('SĐT'),
                Tables\Columns\TextColumn::make('detail')->label('Địa chỉ'),
                Tables\Columns\TextColumn::make('ward')->label('Phường/Xã'),
                Tables\Columns\TextColumn::make('district')->label('Quận/Huyện'),
                Tables\Columns\TextColumn::make('city')->label('Tỉnh/TP'),
                Tables\Columns\IconColumn::make('is_default')->label('Mặc định')->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
