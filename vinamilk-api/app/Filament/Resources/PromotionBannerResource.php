<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionBannerResource\Pages;
use App\Models\PromotionBanner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionBannerResource extends Resource
{
    protected static ?string $model = PromotionBanner::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $modelLabel = 'Banner Khuyến mãi';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Thông tin hiển thị trên card banner')
                ->description('Những thông tin này sẽ hiển thị trực tiếp lên mặt trước của banner.')
                ->schema([
                    Forms\Components\Select::make('campaign_id')
                        ->relationship('campaign', 'name')
                        ->label('Chiến dịch KM')
                        ->nullable()
                        ->searchable()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('title')
                        ->label('Tiêu đề banner')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('subtitle')
                        ->label('Phụ đề (dòng chữ nhỏ bên dưới tiêu đề)')
                        ->placeholder('e.g. Trải nghiệm mua online, nhận tại cửa hàng')
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Ngày bắt đầu hiển thị (trên card)')
                        ->displayFormat('d/m/Y'),
                    Forms\Components\DatePicker::make('end_date')
                        ->label('Ngày kết thúc hiển thị (trên card)')
                        ->displayFormat('d/m/Y'),
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Hình ảnh banner')
                        ->image()
                        ->disk('public')
                        ->directory('promotions/banners')
                        ->imagePreviewHeight('200')
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Vị trí trong lưới (Grid Layout)')
                ->description('Điều chỉnh kích thước của banner trong lưới. Xem preview ở trang danh sách.')
                ->schema([
                    Forms\Components\Select::make('col_span')
                        ->label('Chiều rộng (Số cột chiếm)')
                        ->options([
                            1 => '1 cột (Hẹp)',
                            2 => '2 cột (Rộng)',
                            3 => '3 cột (Toàn chiều rộng)',
                        ])
                        ->default(1)
                        ->required()
                        ->live(),
                    Forms\Components\Select::make('row_span')
                        ->label('Chiều cao (Số hàng chiếm)')
                        ->options([
                            1 => '1 hàng (Thấp)',
                            2 => '2 hàng (Cao)',
                        ])
                        ->default(1)
                        ->required(),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Thứ tự hiển thị (kéo thả để sắp xếp nhanh)')
                        ->numeric()
                        ->default(0)
                        ->helperText('Số nhỏ hơn → hiển thị trước. Bạn cũng có thể kéo thả ở trang danh sách.'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Đang hoạt động')
                        ->default(true),
                    Forms\Components\Toggle::make('is_shown_on_promotions_page')
                        ->label('Hiển thị trên trang Ưu Đãi (/promotions)')
                        ->helperText('Bật để banner này xuất hiện trong lưới Banner Bento ở trang /promotions.')
                        ->default(false),
                ])->columns(2),

            Forms\Components\Section::make('Cấu hình tương tác')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Loại tương tác khi click')
                        ->options([
                            'link'  => '🔗  Chuyển hướng (Link URL)',
                            'modal' => '🪟  Hiển thị Popup chi tiết',
                        ])
                        ->required()
                        ->live()
                        ->default('link'),
                    Forms\Components\TextInput::make('button_text')
                        ->label('Chữ trên nút CTA')
                        ->placeholder('e.g. Mua ngay, Xem ngay, Tham gia')
                        ->maxLength(50),
                    Forms\Components\TextInput::make('link_url')
                        ->label('Đường dẫn URL (chỉ dùng khi chọn Link)')
                        ->placeholder('/collections/sua-tuoi')
                        ->maxLength(255)
                        ->visible(fn(Forms\Get $get) => $get('type') === 'link'),
                ])->columns(2),

            Forms\Components\Section::make('Nội dung Popup (Modal)')
                ->description('Nội dung hiển thị khi người dùng click vào banner. Hỗ trợ bảng biểu, danh sách, định dạng in đậm.')
                ->visible(fn(Forms\Get $get) => $get('type') === 'modal')
                ->schema([
                    Forms\Components\TextInput::make('modal_title')
                        ->label('Tiêu đề popup')
                        ->placeholder('e.g. Quà bung nóc – Deal sốc từng đơn!')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('modal_image_path')
                        ->label('Hình ảnh riêng cho Popup (Tùy chọn)')
                        ->helperText('Nếu để trống, hệ thống sẽ tự động lấy hình ảnh Banner bên ngoài làm ảnh popup.')
                        ->image()
                        ->disk('public')
                        ->directory('promotions/modal_images')
                        ->imagePreviewHeight('200')
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('modal_content')
                        ->label('Nội dung chi tiết (văn bản)')
                        ->toolbarButtons([
                            'bold', 'italic', 'underline', 'strike',
                            'bulletList', 'orderedList',
                            'link', 'h2', 'h3',
                            'undo', 'redo',
                        ])
                        ->columnSpanFull(),
                    Forms\Components\Repeater::make('modal_table_data')
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
                    Forms\Components\TextInput::make('modal_products_limit')
                        ->label('Số lượng sản phẩm tối đa hiển thị trong popup')
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
                    ->height(60)
                    ->width(100)
                    ->extraImgAttributes(['style' => 'object-fit:cover;border-radius:6px']),
                Tables\Columns\TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn($record) => $record->subtitle),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Ngày kết thúc')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Loại')
                    ->colors([
                        'primary' => 'link',
                        'success' => 'modal',
                    ])
                    ->formatStateUsing(fn($state) => $state === 'link' ? 'Link' : 'Popup'),
                Tables\Columns\TextColumn::make('col_span')
                    ->label('Cột')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn($state) => "{$state} cột"),
                Tables\Columns\TextColumn::make('row_span')
                    ->label('Hàng')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn($state) => "{$state} hàng"),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Hiển thị')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->sortable(),
            ])
            ->filters([])
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPromotionBanners::route('/'),
            'create' => Pages\CreatePromotionBanner::route('/create'),
            'edit'   => Pages\EditPromotionBanner::route('/{record}/edit'),
            'grid'   => Pages\VisualGridBuilder::route('/grid'),
        ];
    }
}
