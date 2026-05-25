<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CareProductResource\Pages;
use App\Models\CareProduct;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class CareProductResource extends Resource
{
    protected static ?string $model = CareProduct::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Vinamilk Care';
    protected static ?string $navigationLabel = 'Sản phẩm Care';
    protected static ?string $modelLabel = 'Sản phẩm Care';
    protected static ?string $pluralModelLabel = 'Sản phẩm trong chương trình Care';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Chọn sản phẩm bán trong Vinamilk Care')
                ->description('Chọn từ danh mục sản phẩm. Khách sẽ tự chọn biến thể và số lượng khi đăng ký (giống trang chi tiết sản phẩm / giỏ hàng). Quà tặng do hệ thống khuyến mãi tự áp dụng theo điều kiện đơn hàng.')
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->label('Sản phẩm')
                        ->options(function () {
                            $existing = CareProduct::pluck('product_id')->filter();
                            return Product::query()
                                ->where('status', 'published')
                                ->when(
                                    request()->route('record'),
                                    fn ($q) => $q,
                                    fn ($q) => $q->whereNotIn('id', $existing)
                                )
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Product $p) => [
                                    $p->id => static::productOptionHtml($p),
                                ]);
                        })
                        ->allowHtml()
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search) {
                            return Product::query()
                                ->where('status', 'published')
                                ->where('name', 'like', "%{$search}%")
                                ->orderBy('name')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (Product $p) => [
                                    $p->id => static::productOptionHtml($p),
                                ]);
                        })
                        ->getOptionLabelUsing(function ($value): string {
                            $product = Product::find($value);
                            return $product ? static::productOptionHtml($product) : '';
                        })
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Hiển thị trên /care')
                        ->default(true),
                ]),
        ]);
    }

    public static function productOptionHtml(Product $product): string
    {
        return view('filament.forms.product-select-option', ['product' => $product])->render();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->columns([
                Tables\Columns\ImageColumn::make('product.main_image')
                    ->label('Ảnh')
                    ->disk('public')
                    ->height(48)
                    ->width(48),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Sản phẩm')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('product.category.name')
                    ->label('Danh mục')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Hiển thị /care')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCareProducts::route('/'),
            'create' => Pages\CreateCareProduct::route('/create'),
            'edit'   => Pages\EditCareProduct::route('/{record}/edit'),
        ];
    }
}
