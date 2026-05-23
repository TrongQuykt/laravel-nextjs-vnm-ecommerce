<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatSettingResource\Pages;
use App\Filament\Resources\ChatSettingResource\RelationManagers;
use App\Models\ChatSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChatSettingResource extends Resource
{
    protected static ?string $model = ChatSetting::class;

    protected static ?string $navigationGroup = 'Cấu hình Chatbot';
    protected static ?string $navigationLabel = 'Định hình nhân cách';
    protected static ?string $modelLabel = 'Nhân cách';
    protected static ?string $navigationIcon = 'heroicon-o-identification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('Mã cấu hình')
                    ->disabled()
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->label('Mô tả')
                    ->required(),
                Forms\Components\Textarea::make('value')
                    ->label('Nội dung (System Instruction)')
                    ->required()
                    ->rows(10)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')->label('Mô tả'),
                Tables\Columns\TextColumn::make('value')->label('Nội dung')->limit(50),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageChatSettings::route('/'),
        ];
    }
}
