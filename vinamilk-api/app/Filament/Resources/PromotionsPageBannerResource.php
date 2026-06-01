<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionsPageBannerResource\Pages;
use App\Models\PromotionsPageBanner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionsPageBannerResource extends Resource
{
    protected static ?string $model = PromotionsPageBanner::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Khuyến mãi';
    protected static ?string $navigationLabel = 'Banner trang Ưu Đãi';
    protected static ?string $modelLabel = 'Banner trang Ưu Đãi';
    protected static ?string $pluralModelLabel = 'Banner trang Ưu Đãi';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Thông tin banner')
                ->description('Quản lý lưới Banner Bento trên trang /promotions (Ưu Đãi).')
                ->schema([
                    Forms\Components\Select::make('layout_slot')
                        ->label('Vị trí trong lưới Bento')
                        ->options([
                            'hero'  => 'Banner lớn bên trái (Hero)',
                            'side'  => 'Banner nhỏ cột phải (tối đa 3)',
                            'extra' => 'Banner hàng phụ bên dưới',
                        ])
                        ->default('side')
                        ->required()
                        ->helperText('Hero: 1 banner lớn. Side: tối đa 3 banner xếp dọc. Extra: các banner còn lại.'),
                    Forms\Components\TextInput::make('title')
                        ->label('Tiêu đề (dùng cho alt & quản trị)')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('subtitle')
                        ->label('Phụ đề (tùy chọn)')
                        ->maxLength(255),
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Hình ảnh banner')
                        ->image()
                        ->disk('public')
                        ->directory('promotions/page-banners')
                        ->imagePreviewHeight('200')
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Thứ tự trong cùng vị trí')
                        ->numeric()
                        ->default(0)
                        ->helperText('Số nhỏ hơn hiển thị trước. Kéo thả ở danh sách để sắp xếp nhanh.'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Đang hiển thị')
                        ->default(true),
                ])->columns(2),

            Forms\Components\Section::make('Tương tác khi click')
                ->schema([
                    Forms\Components\Select::make('promotion_banner_id')
                        ->label('Banner khuyến mãi liên kết (/khuyen-mai)')
                        ->relationship(
                            'promotionBanner',
                            'title',
                            fn ($query) => $query->where('is_active', true)->orderBy('sort_order')
                        )
                        ->searchable()
                        ->preload()
                        ->helperText('Bắt buộc nếu loại Popup: khi click từ /promotions sẽ sang /khuyen-mai và tự mở modal banner này.')
                        ->visible(fn (Forms\Get $get) => $get('type') === 'modal')
                        ->required(fn (Forms\Get $get) => $get('type') === 'modal'),
                    Forms\Components\Select::make('type')
                        ->label('Loại')
                        ->options([
                            'link'  => 'Chuyển hướng (Link URL)',
                            'modal' => 'Hiển thị Popup chi tiết',
                        ])
                        ->required()
                        ->live()
                        ->default('link'),
                    Forms\Components\TextInput::make('button_text')
                        ->label('Chữ nút CTA')
                        ->placeholder('Xem ngay')
                        ->maxLength(50),
                    Forms\Components\TextInput::make('link_url')
                        ->label('Đường dẫn URL')
                        ->placeholder('/collections/sua-tuoi')
                        ->maxLength(255)
                        ->visible(fn (Forms\Get $get) => $get('type') === 'link'),
                ])->columns(2),

            Forms\Components\Section::make('Nội dung Popup (Modal)')
                ->visible(fn (Forms\Get $get) => $get('type') === 'modal')
                ->schema([
                    Forms\Components\TextInput::make('modal_title')
                        ->label('Tiêu đề popup')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('modal_image_path')
                        ->label('Hình ảnh popup (tùy chọn)')
                        ->image()
                        ->disk('public')
                        ->directory('promotions/page-modal-images')
                        ->imagePreviewHeight('200')
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('modal_content')
                        ->label('Nội dung chi tiết')
                        ->toolbarButtons([
                            'bold', 'italic', 'underline', 'strike',
                            'bulletList', 'orderedList',
                            'link', 'h2', 'h3',
                            'undo', 'redo',
                        ])
                        ->columnSpanFull(),
                    Forms\Components\Repeater::make('modal_table_data')
                        ->label('Bảng dữ liệu (tùy chọn)')
                        ->helperText('Chèn vào nội dung bằng [TABLE:Mã], ví dụ [TABLE:1]')
                        ->schema([
                            Forms\Components\TextInput::make('table_id')
                                ->label('Mã bảng')
                                ->default('1')
                                ->required(),
                            Forms\Components\Repeater::make('rows')
                                ->label('Các dòng')
                                ->schema([
                                    Forms\Components\TextInput::make('col1')->label('Cột 1'),
                                    Forms\Components\TextInput::make('col2')->label('Cột 2'),
                                    Forms\Components\TextInput::make('col3')->label('Cột 3'),
                                    Forms\Components\TextInput::make('col4')->label('Cột 4'),
                                    Forms\Components\TextInput::make('col5')->label('Cột 5'),
                                ])
                                ->columns(5)
                                ->defaultItems(1)
                                ->collapsible(),
                        ])
                        ->addActionLabel('Thêm bảng')
                        ->defaultItems(0)
                        ->collapsible()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('modal_products_limit')
                        ->label('Số sản phẩm tối đa trong popup')
                        ->numeric()
                        ->default(9),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Ảnh')
                    ->disk('public')
                    ->height(56)
                    ->width(90)
                    ->extraImgAttributes(['style' => 'object-fit:cover;border-radius:6px']),
                Tables\Columns\TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('promotionBanner.title')
                    ->label('Banner /khuyen-mai')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('layout_slot')
                    ->label('Vị trí')
                    ->colors([
                        'primary' => 'hero',
                        'success' => 'side',
                        'gray'    => 'extra',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'hero'  => 'Hero (trái)',
                        'side'  => 'Cột phải',
                        default => 'Hàng dưới',
                    }),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Loại')
                    ->colors([
                        'primary' => 'link',
                        'success' => 'modal',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'link' ? 'Link' : 'Popup'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Hiển thị')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('layout_slot')
                    ->label('Vị trí')
                    ->options([
                        'hero'  => 'Hero',
                        'side'  => 'Cột phải',
                        'extra' => 'Hàng dưới',
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPromotionsPageBanners::route('/'),
            'create' => Pages\CreatePromotionsPageBanner::route('/create'),
            'edit'   => Pages\EditPromotionsPageBanner::route('/{record}/edit'),
        ];
    }
}
