<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Traits\HasRolePermissions;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Volume;
use App\Models\PackagingType;
use App\Models\Flavor;
use App\Services\ActivityLogger;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    use HasRolePermissions;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Sản phẩm';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->name;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['homeFeaturedVolume', 'variants', 'volumeMedia.volume']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Product Details')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('General')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Forms\Components\Section::make('Hierarchy')
                                    ->description('Organize this product into the Vinamilk classification system.')
                                    ->schema([
                                        Forms\Components\Select::make('brand_id')
                                            ->label('Thương hiệu')
                                            ->relationship('brand', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function (Forms\Set $set) {
                                                $set('category_id', null);
                                                $set('product_line_id', null);
                                            }),
                                        Forms\Components\Select::make('category_id')
                                            ->label('Danh mục')
                                            ->options(function (Forms\Get $get) {
                                                $brandId = $get('brand_id');
                                                if (!$brandId) return [];
                                                return \App\Models\Category::where('brand_id', $brandId)
                                                    ->pluck('name', 'id');
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->disabled(fn (Forms\Get $get) => !$get('brand_id'))
                                            ->helperText(fn (Forms\Get $get) => !$get('brand_id') ? 'Chọn Thương hiệu trước' : null)
                                            ->afterStateUpdated(fn (Forms\Set $set) => $set('product_line_id', null)),
                                        Forms\Components\Select::make('product_line_id')
                                            ->label('Dòng sản phẩm')
                                            ->options(function (Forms\Get $get) {
                                                $categoryId = $get('category_id');
                                                if (!$categoryId) return [];
                                                return \App\Models\ProductLine::where('category_id', $categoryId)
                                                    ->pluck('name', 'id');
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->disabled(fn (Forms\Get $get) => !$get('category_id'))
                                            ->helperText(fn (Forms\Get $get) => !$get('category_id') ? 'Chọn Danh mục trước' : null),
                                    ])->columns(2),

                                Forms\Components\Section::make('Identity')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (?string $state, Forms\Set $set) => $set('slug', Str::slug((string) $state))),
                                        Forms\Components\TextInput::make('slug')
                                            ->required()
                                            ->unique(Product::class, 'slug', ignoreRecord: true),
                                        
                                        Forms\Components\Select::make('status')
                                            ->options([
                                                'draft' => 'Draft',
                                                'published' => 'Published',
                                                'archived' => 'Archived',
                                            ])
                                            ->default('draft')
                                            ->required(),

                                        Forms\Components\Select::make('home_featured_volume_id')
                                            ->label('Dung tích hiển thị trên Trang chủ (Mời bạn sắm sửa)')
                                            ->options(function ($record) {
                                                if (!$record) return [];
                                                // Lấy các dung tích đã có trong Media Gallery
                                                return $record->volumeMedia->mapWithKeys(fn ($vm) => [
                                                    $vm->id => $vm->volume?->name ?? 'Dung tích #' . $vm->id
                                                ]);
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Chọn dung tích từ Media Gallery để hiển thị'),

                                        Forms\Components\Toggle::make('is_search_featured')
                                            ->label('Hiển thị trong Gợi ý tìm kiếm (Dành cho bạn)')
                                            ->default(false),

                                        Forms\Components\TextInput::make('loyalty_rate')
                                            ->label('Tỷ lệ tích điểm (%)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->step(0.1)
                                            ->suffix('%')
                                            ->helperText('Nhập để GHI ĐÈ tỷ lệ của Danh mục. Để trống nếu muốn lấy theo Danh mục.'),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Description & Instructions')
                            ->icon('heroicon-m-document-text')
                            ->schema([
                                Forms\Components\RichEditor::make('short_description')
                                    ->label('Mô tả ngắn')
                                    ->columnSpanFull(),
                                
                                Forms\Components\TextInput::make('description_title')
                                    ->label('Tiêu đề phần Mô tả')
                                    ->placeholder('e.g. Chi tiết sản phẩm')
                                    ->default('Chi tiết sản phẩm'),

                                Forms\Components\FileUpload::make('description_image')
                                    ->label('Ảnh minh họa cho Mô tả (Infographic) - 1 ảnh')
                                    ->image()
                                    ->disk('public')
                                    ->directory('products/description')
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('description_images')
                                    ->label('Danh sách ảnh minh họa (Hiển thị sát nhau, full-bleed)')
                                    ->image()
                                    ->multiple()
                                    ->reorderable()
                                    ->disk('public')
                                    ->directory('products/description')
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('description')
                                    ->label('Nội dung mô tả chi tiết')
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('ingredients')
                                    ->label('Thành phần')
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('usage_instructions')
                                    ->label('Hướng dẫn sử dụng')
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('storage_instructions')
                                    ->label('Hướng dẫn bảo quản')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Nutrition & Attributes')
                            ->icon('heroicon-m-bolt')
                            ->schema([
                                Forms\Components\Section::make('Bảng thông tin dinh dưỡng')
                                    ->description('Nhập thông tin dinh dưỡng chi tiết (Nutrition Facts).')
                                    ->schema([
                                        Forms\Components\Repeater::make('nutrition_facts')
                                            ->label('Dưỡng chất')
                                            ->schema([
                                                Forms\Components\TextInput::make('key')->label('Dưỡng chất')->placeholder('e.g. Canxi')->required(),
                                                Forms\Components\TextInput::make('value')->label('Hàm lượng')->placeholder('e.g. 120')->required(),
                                                Forms\Components\TextInput::make('unit')->label('Đơn vị')->placeholder('e.g. mg')->required(),
                                            ])
                                            ->columns(3)
                                            ->defaultItems(0),
                                    ]),

                                Forms\Components\Section::make('Phân loại sản phẩm (Filters)')
                                    ->description('Phân loại sản phẩm theo nhu cầu, mức đường và độ tuổi để khách hàng dễ dàng tìm kiếm.')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('nutritionalNeeds')
                                            ->relationship('nutritionalNeeds', 'name')
                                            ->label('Nhu cầu dinh dưỡng')
                                             ->columns(3)
                                            ->gridDirection('row'),
                                        
                                        Forms\Components\Select::make('sugar_level_id')
                                            ->relationship('sugarLevel', 'name')
                                            ->label('Mức đường')
                                            ->searchable(),

                                        Forms\Components\CheckboxList::make('ageGroups')
                                            ->relationship('ageGroups', 'name')
                                            ->label('Độ tuổi')
                                            ->columns(3)
                                            ->gridDirection('row'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Features & Comparison')
                            ->icon('heroicon-m-clipboard-document-list')
                            ->schema([
                                Forms\Components\Section::make('Có gì đặc sắc')
                                    ->description('Chọn 1 ảnh duy nhất bên trái và danh sách đặc điểm bên phải.')
                                    ->schema([
                                        Forms\Components\TextInput::make('features_title')
                                            ->label('Tiêu đề khu vực Đặc sắc')
                                            ->default('Có gì đặc sắc'),
                                        
                                        Forms\Components\FileUpload::make('features_main_image')
                                            ->label('Ảnh minh họa chính (Sticky)')
                                            ->image()
                                            ->disk('public')
                                            ->directory('products/features')
                                            ->columnSpanFull(),

                                        Forms\Components\Repeater::make('features')
                                            ->label('Danh sách các điểm đặc sắc (Chỉ nhập chữ)')
                                            ->schema([
                                                Forms\Components\TextInput::make('title')
                                                    ->label('Tiêu đề đặc điểm')
                                                    ->required(),
                                                Forms\Components\RichEditor::make('content')
                                                    ->label('Nội dung chi tiết')
                                                    ->required(),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Đặc điểm mới')
                                            ->collapsible(),
                                    ]),

                                Forms\Components\Section::make('Bảng so sánh')
                                    ->description('Cấu hình bảng so sánh ngang (Tối đa 5 cột).')
                                    ->schema([
                                        Forms\Components\TextInput::make('comparison_title')
                                            ->label('Tiêu đề bảng so sánh')
                                            ->default('Bảng so sánh'),
                                        
                                        Forms\Components\Repeater::make('comparison_table_headers')
                                            ->label('1. Cấu hình Tên các Cột (BẮT BUỘC ĐỂ HIỆN BẢNG)')
                                            ->schema([
                                                Forms\Components\TextInput::make('key')
                                                    ->label('Mã cột (v1, v2...)')
                                                    ->required(),
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Tên hiển thị (VD: Vinamilk)')
                                                    ->required(),
                                            ])
                                            ->grid(5)
                                            ->maxItems(5),

                                        Forms\Components\Repeater::make('comparison_table_rows')
                                            ->label('2. Dòng dữ liệu so sánh (Dàn hàng ngang)')
                                            ->schema([
                                                Forms\Components\Grid::make(6)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('attribute')
                                                            ->label('Tiêu chí')
                                                            ->required()
                                                            ->columnSpan(1),
                                                        
                                                        Forms\Components\TextInput::make('v1')->label('Cột 1')->default('dot'),
                                                        Forms\Components\TextInput::make('v2')->label('Cột 2')->default('dash'),
                                                        Forms\Components\TextInput::make('v3')->label('Cột 3')->default('dash'),
                                                        Forms\Components\TextInput::make('v4')->label('Cột 4'),
                                                        Forms\Components\TextInput::make('v5')->label('Cột 5'),
                                                    ]),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['attribute'] ?? 'Dòng mới'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Section Highlights')
                            ->icon('heroicon-m-sparkles')
                            ->schema([
                                Forms\Components\Section::make('Biểu tượng Đặc trưng')
                                    ->description('Tích chọn các biểu tượng đặc trưng của sản phẩm này.')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('specialHighlights')
                                            ->relationship('specialHighlights', 'name')
                                            ->label('Danh sách biểu tượng đặc trưng')
                                            ->columns(4)
                                            ->bulkToggleable()
                                            ->searchable(),
                                    ]),

                                Forms\Components\Section::make('Biểu tượng Chứng nhận')
                                    ->description('Tích chọn các chứng nhận của sản phẩm này và chọn 1 thẻ để hiển thị trên danh mục.')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('certificates')
                                            ->relationship('certificates', 'name')
                                            ->label('Danh sách chứng nhận')
                                            ->columns(4)
                                            ->bulkToggleable()
                                            ->searchable()
                                            ->live(),
                                        
                                        Forms\Components\Select::make('card_tag_id')
                                            ->relationship('cardTag', 'name')
                                            ->label('Thẻ hiển thị trên Card sản phẩm')
                                            ->placeholder('Chọn chứng nhận làm tag nổi bật')
                                            ->searchable()
                                            ->preload(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Variants')
                            ->icon('heroicon-m-squares-2x2')
                            ->schema([
                                Forms\Components\Repeater::make('variants')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Section::make()
                                            ->schema([
                                                Forms\Components\Select::make('flavor_id')
                                                    ->relationship('flavor', 'name')
                                                    ->label('Hương vị')
                                                    ->searchable()
                                                    ->live()
                                                    ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => static::updateVariantSku($set, $get)),
                                                Forms\Components\Select::make('volume_id')
                                                    ->relationship('volume', 'name')
                                                    ->label('Thể tích/Khối lượng')
                                                    ->searchable()
                                                    ->live()
                                                    ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => static::updateVariantSku($set, $get)),
                                                Forms\Components\Select::make('packaging_type_id')
                                                    ->relationship('packagingType', 'name')
                                                    ->label('Quy cách đóng gói')
                                                    ->required()
                                                    ->searchable()
                                                    ->live()
                                                    ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => static::updateVariantSku($set, $get)),
                                                Forms\Components\TextInput::make('sku')->label('Mã SKU')->required()->unique('product_variants', 'sku', ignoreRecord: true)->disabled()->dehydrated(),
                                                
                                                Forms\Components\Group::make([
                                                    Forms\Components\TextInput::make('base_price')
                                                        ->label('Giá gốc')
                                                        ->numeric()
                                                        ->step(0.001)
                                                        ->required()
                                                        ->prefix('VND')
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                            $discount = (int) ($get('discount_percentage') ?? 0);
                                                            $price = round((float) $state * (1 - $discount / 100), 3);
                                                            $set('price', number_format($price, 3, '.', ''));
                                                        }),
                                                    Forms\Components\TextInput::make('discount_percentage')
                                                        ->label('% Giảm giá')
                                                        ->numeric()
                                                        ->default(0)
                                                        ->suffix('%')
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                            $basePrice = (float) ($get('base_price') ?? 0);
                                                            $price = round($basePrice * (1 - (int) $state / 100), 3);
                                                            $set('price', number_format($price, 3, '.', ''));
                                                        }),
                                                    Forms\Components\TextInput::make('price')
                                                        ->label('Giá bán cuối cùng')
                                                        ->numeric()
                                                        ->disabled()
                                                        ->dehydrated()
                                                        ->prefix('VND'),
                                                ])->columns(3)->columnSpanFull(),

                                                Forms\Components\Section::make('Quản lý tồn kho')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('stock_quantity')
                                                            ->label('Số lượng tồn kho')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->helperText('Tổng số lượng hàng trong kho'),
                                                        Forms\Components\TextInput::make('reserved_quantity')
                                                            ->label('Số lượng đang giữ')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->disabled()
                                                            ->dehydrated(false)
                                                            ->helperText('Số lượng đang được giữ cho đơn hàng chờ thanh toán'),
                                                        Forms\Components\TextInput::make('available_quantity')
                                                            ->label('Số lượng có sẵn')
                                                            ->numeric()
                                                            ->disabled()
                                                            ->dehydrated(false)
                                                            ->helperText('Số lượng có thể bán (Tồn kho - Đang giữ)'),
                                                        Forms\Components\Grid::make(2)->schema([
                                                            Forms\Components\TextInput::make('low_stock_threshold')
                                                                ->label('Ngưỡng cảnh báo thấp')
                                                                ->numeric()
                                                                ->default(10)
                                                                ->helperText('Cảnh báo khi số lượng có sẵn dưới mức này'),
                                                            Forms\Components\TextInput::make('out_of_stock_threshold')
                                                                ->label('Ngưỡng cảnh báo hết hàng')
                                                                ->numeric()
                                                                ->default(0)
                                                                ->helperText('Cảnh báo khi số lượng bằng hoặc dưới mức này'),
                                                        ]),
                                                    ])->columns(2),
                                                Forms\Components\Section::make('Đóng gói')
                                                    ->schema([
                                                        Forms\Components\Grid::make(2)->schema([
                                                            Forms\Components\TextInput::make('units_per_pack')
                                                                ->label('Số lượng lẻ/Gói bán')
                                                                ->helperText('VD: 1 lốc có 4 hộp, hoặc 1 pack có 24 hộp')
                                                                ->numeric()
                                                                ->default(1)
                                                                ->required(),
                                                            Forms\Components\TextInput::make('units_per_case')
                                                                ->label('Số lượng lẻ/Thùng')
                                                                ->helperText('VD: 1 thùng quy chuẩn có 48 hộp')
                                                                ->numeric()
                                                                ->default(48)
                                                                ->required(),
                                                        ]),
                                                        Forms\Components\Toggle::make('is_active')->label('Đang bán')->default(true),
                                                    ]),
                                            ])->columns(2),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => ($state['sku'] ?? 'Mới'))
                                    ->cloneable()
                                    ->collapsed(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Media Gallery')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                Forms\Components\Repeater::make('volumeMedia')
                                    ->label('Quản lý hình ảnh theo Dung tích (Volume)')
                                    ->relationship()
                                    ->schema([
                                        # Volume Select
                                        Forms\Components\Select::make('volume_id')
                                            ->label('Dung tích')
                                            ->options(function (Forms\Get $get) {
                                                $productVariants = $get('../../variants') ?? [];
                                                $volumeIds = collect($productVariants)->pluck('volume_id')->filter()->unique();
                                                if ($volumeIds->isEmpty()) return \App\Models\Volume::pluck('name', 'id');
                                                return \App\Models\Volume::whereIn('id', $volumeIds)->pluck('name', 'id');
                                            })
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->distinct(),

                                        Forms\Components\FileUpload::make('main_image')
                                            ->label('Ảnh chính cho dung tích này')
                                            ->image()
                                            ->disk('public')
                                            ->directory('products/volumes/main')
                                            ->imageResizeMode('cover')
                                            ->imageCropAspectRatio('1:1')
                                            ->imageResizeTargetWidth('800')
                                            ->imageResizeTargetHeight('800')
                                            ->required(),

                                        Forms\Components\Repeater::make('images')
                                            ->label('Bộ sưu tập ảnh chi tiết (Gallery)')
                                            ->schema([
                                                Forms\Components\FileUpload::make('path')
                                                    ->label('Ảnh')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('products/volumes/gallery')
                                                    ->required(),
                                                Forms\Components\TextInput::make('position')
                                                    ->label('Vị trí')
                                                    ->numeric()
                                                    ->default(function (Forms\Get $get) {
                                                        $images = $get('../../images') ?? [];
                                                        return max(0, count($images) - 1);
                                                    }),
                                            ])
                                            ->columns(2)
                                            ->grid(2)
                                            ->reorderableWithButtons()
                                            ->itemLabel(fn (array $state): ?string => 'Vị trí: ' . ($state['position'] ?? '0'))
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->itemLabel(fn (array $state) => \App\Models\Volume::find($state['volume_id'])?->name ?? 'Dung tích mới')
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->collapsed(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image_display')
                    ->label('Ảnh')
                    ->disk('public')
                    ->circular()
                    ->size(60)
                    ->getStateUsing(function ($record) {
                        if ($record->home_featured_volume_id) {
                            $volumeMedia = $record->volumeMedia->where('id', $record->home_featured_volume_id)->first();
                            if ($volumeMedia) return $volumeMedia->main_image;
                        }
                        return $record->volumeMedia->first()?->main_image;
                    }),
                Tables\Columns\TextColumn::make('name')
                    ->label('Sản phẩm')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Thương hiệu')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Danh mục')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'danger',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'draft' => 'Nháp',
                        'published' => 'Đã xuất bản',
                        'archived' => 'Lưu trữ',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_home_featured')
                    ->label('Nổi bật')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('Danh mục')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->label('Thương hiệu')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'draft' => 'Nháp',
                        'published' => 'Đã xuất bản',
                        'archived' => 'Lưu trữ',
                    ]),
                Tables\Filters\TernaryFilter::make('is_home_featured')
                    ->label('Sản phẩm nổi bật')
                    ->placeholder('Tất cả')
                    ->trueLabel('Nổi bật')
                    ->falseLabel('Bình thường'),
                Tables\Filters\Filter::make('created_at')
                    ->label('Ngày tạo')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Từ ngày'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Đến ngày'),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['from'] ?? null) {
                            $query->where('created_at', '>=', $data['from']);
                        }
                        if ($data['until'] ?? null) {
                            $query->where('created_at', '<=', $data['until']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Xuất bản')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['status' => 'published']);
                            }
                        }),
                    Tables\Actions\BulkAction::make('archive')
                        ->label('Lưu trữ')
                        ->icon('heroicon-o-archive-box')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['status' => 'archived']);
                            }
                        }),
                ]),
            ])
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function updateVariantSku(Forms\Set $set, Forms\Get $get): void
    {
        $productName = (string) ($get('../../name') ?? 'vnm');
        $flavor =Flavor::find($get('flavor_id'))?->name ?? '';
        $volume = Volume::find($get('volume_id'))?->name ?? '';
        $packaging = PackagingType::find($get('packaging_type_id'))?->name ?? '';

        $parts = array_filter([$productName, $flavor, $volume, $packaging]);
        $set('sku', Str::slug(implode('-', $parts)));
    }
}
