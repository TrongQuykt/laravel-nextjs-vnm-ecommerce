<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatMessageResource\Pages;
use App\Filament\Resources\ChatMessageResource\RelationManagers;
use App\Models\ChatMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChatMessageResource extends Resource
{
    protected static ?string $model = ChatMessage::class;

    protected static ?string $navigationGroup = 'Cấu hình Chatbot';
    protected static ?string $navigationLabel = 'Lịch sử trò chuyện';
    protected static ?string $modelLabel = 'Tin nhắn';
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('session_id')->label('Session ID'),
                Forms\Components\TextInput::make('role')->label('Vai trò'),
                Forms\Components\Textarea::make('content')->label('Nội dung')->columnSpanFull(),
                Forms\Components\TextInput::make('ip_address')->label('IP Address'),
                Forms\Components\DateTimePicker::make('created_at')->label('Thời gian'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('session_id')->label('Phiên chat')->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Người gửi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'user' => 'gray',
                        'bot' => 'success',
                    }),
                Tables\Columns\TextColumn::make('content')->label('Nội dung')->limit(100)->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Thời gian')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListChatMessages::route('/'),
            'view' => Pages\ViewChatMessage::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
