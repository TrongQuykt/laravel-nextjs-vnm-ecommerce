<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventNewsResource\Pages;
use App\Filament\Traits\HasRolePermissions;
use App\Models\EventNews;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EventNewsResource extends Resource
{
    use HasRolePermissions;

    protected static ?string $model = EventNews::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Tin sự kiện';

    protected static ?string $modelLabel = 'Tin sự kiện';

    protected static ?string $pluralModelLabel = 'Tin sự kiện';

    protected static ?string $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin chung')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Tiêu đề sự kiện')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug (Đường dẫn)')
                                    ->required()
                                    ->unique(EventNews::class, 'slug', ignoreRecord: true),
                            ]),
                        Forms\Components\Textarea::make('excerpt')
                            ->label('Mô tả ngắn')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Nội dung chi tiết')
                    ->schema([
                        Forms\Components\FileUpload::make('banner_image')
                            ->label('Ảnh bìa sự kiện (Banner Card)')
                            ->image()
                            ->directory('event-banners')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('content')
                            ->label('Nội dung mô tả')
                            ->required()
                            ->fileAttachmentsDirectory('event-content')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('table_description')
                            ->label('Mô tả bảng (chi tiết sự kiện)')
                            ->fileAttachmentsDirectory('event-content')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Xuất bản')
                    ->schema([
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Ngày đăng')
                            ->default(now()),
                        Forms\Components\Toggle::make('is_published')
                            ->label('Đã xuất bản')
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('banner_image')
                    ->label('Ảnh'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Ngày đăng')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_published')
                    ->label('Trạng thái')
                    ->options([
                        '1' => 'Đã xuất bản',
                        '0' => 'Nháp',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
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
            'index' => Pages\ListEventNews::route('/'),
            'create' => Pages\CreateEventNews::route('/create'),
            'edit' => Pages\EditEventNews::route('/{record}/edit'),
        ];
    }
}
