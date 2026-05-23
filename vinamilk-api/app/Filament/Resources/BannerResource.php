<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Filament\Resources\BannerResource\RelationManagers;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'CMS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Banner Information')
                    ->description('Manage display banners for your storefront.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('subtitle')
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('banners')
                            ->required(),
                        Forms\Components\TextInput::make('link')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Select::make('position')
                            ->options([
                                'home_hero' => 'Trang chủ: Dải banner chính (Hero)',
                                'rewards_hero' => 'Trang đổi quà: Banner chính (Rewards)',
                                'home_promo_left' => 'Trang chủ: Khối khuyến mãi (Bên trái)',
                                'home_promo_right' => 'Trang chủ: Khối khuyến mãi (Bên phải)',
                            ])
                            ->default('home_hero')
                            ->required(),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Thứ tự hiển thị')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('show_text')
                            ->label('Hiển thị nội dung trên Banner')
                            ->default(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Visible on site')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Promo Details (Gelato Style)')
                    ->description('Specific settings for the home promo right banner with the lime green box.')
                    ->hidden(fn (Forms\Get $get) => $get('position') !== 'home_promo_right')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Sản phẩm liên kết')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Textarea::make('box_text')
                            ->label('Nội dung Hộp (Ví dụ: Mới!\nMới!\nMới!)')
                            ->rows(3)
                            ->placeholder("Mới!\nMới!\nMới!")
                            ->helperText('Dùng phím Enter để xuống dòng.'),

                        Forms\Components\TextInput::make('box_subtitle')
                            ->label('Nhãn sản phẩm (Ví dụ: Kem Vinamilk Gelato)')
                            ->placeholder('Kem Vinamilk Gelato'),
                    ])
                    ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('link')
                    ->color('gray')
                    ->limit(30),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->color('gray')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active only'),
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
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
