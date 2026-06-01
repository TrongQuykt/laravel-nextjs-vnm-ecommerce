<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatKnowledgeResource\Pages;
use App\Filament\Resources\ChatKnowledgeResource\RelationManagers;
use App\Models\ChatKnowledge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChatKnowledgeResource extends Resource
{
    protected static ?string $model = ChatKnowledge::class;

    protected static ?string $navigationGroup = 'Hệ thống';
    protected static ?string $navigationLabel = 'Trí nhớ doanh nghiệp';
    protected static ?string $modelLabel = 'Kiến thức';
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('question')
                    ->label('Chủ đề / Câu hỏi')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('answer')
                    ->label('Nội dung trả lời / Kiến thức')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Đang hoạt động')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question')->label('Chủ đề')->searchable(),
                Tables\Columns\TextColumn::make('answer')->label('Kiến thức')->limit(50),
                Tables\Columns\IconColumn::make('is_active')->label('Kích hoạt')->boolean(),
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
            'index' => Pages\ListChatKnowledge::route('/'),
            'create' => Pages\CreateChatKnowledge::route('/create'),
            'edit' => Pages\EditChatKnowledge::route('/{record}/edit'),
        ];
    }
}
