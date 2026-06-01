<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionTermResource\Pages;
use App\Filament\Resources\PromotionTermResource\RelationManagers;
use App\Models\PromotionTerm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotionTermResource extends Resource
{
    protected static ?string $model = PromotionTerm::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Khuyến mãi';
    protected static ?string $modelLabel = 'Thể lệ chương trình';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\Section::make('Thể lệ chi tiết')
                ->schema([
                    Forms\Components\Select::make('campaign_id')
                        ->relationship('campaign', 'name')
                        ->label('Chiến dịch KM')
                        ->nullable()
                        ->searchable()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('title')
                        ->label('Tiêu đề mục')
                        ->placeholder('e.g. Thông tin chương trình')
                        ->required()
                        ->maxLength(255),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Thứ tự')
                            ->numeric()
                            ->default(0),
                        Forms\Components\RichEditor::make('content')
                            ->label('Nội dung chi tiết')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('table_data')
                            ->label('Danh sách các Bảng dữ liệu (Tùy chọn)')
                            ->helperText('Nhấn "Thêm Bảng" để tạo nhiều bảng. Chèn vào nội dung bằng cách gõ [TABLE:Mã]. Ví dụ: [TABLE:1], [TABLE:2]')
                            ->schema([
                                Forms\Components\TextInput::make('table_id')
                                    ->label('Mã chèn bảng (Ví dụ: 1)')
                                    ->placeholder('e.g. 1')
                                    ->default('1')
                                    ->required(),
                                Forms\Components\Repeater::make('rows')
                                    ->label('Dữ liệu các dòng trong bảng')
                                    ->schema([
                                        Forms\Components\TextInput::make('col1')->label('Cột 1'),
                                        Forms\Components\TextInput::make('col2')->label('Cột 2'),
                                        Forms\Components\TextInput::make('col3')->label('Cột 3'),
                                        Forms\Components\TextInput::make('col4')->label('Cột 4 (Tùy chọn)'),
                                        Forms\Components\TextInput::make('col5')->label('Cột 5 (Tùy chọn)'),
                                    ])
                                    ->columns(5)
                                    ->defaultItems(1)
                                    ->addActionLabel('Thêm hàng vào bảng này')
                                    ->reorderableWithButtons()
                                    ->collapsible(),
                            ])
                            ->addActionLabel('Thêm một Bảng mới')
                            ->defaultItems(0)
                            ->default([])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => "Bảng mã: " . ($state['table_id'] ?? 'Chưa đặt tên'))
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListPromotionTerms::route('/'),
            'create' => Pages\CreatePromotionTerm::route('/create'),
            'edit' => Pages\EditPromotionTerm::route('/{record}/edit'),
        ];
    }
}
