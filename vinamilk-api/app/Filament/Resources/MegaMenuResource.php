<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MegaMenuResource\Pages;
use App\Models\MegaMenu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Product;

class MegaMenuResource extends Resource
{
    protected static ?string $model = MegaMenu::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-4';

    protected static ?string $navigationGroup = 'Hệ thống';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General settings')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('url')
                            ->maxLength(255)
                            ->label('Direct Link (URL)')
                            ->helperText('If not provided, the menu will just act as a dropdown trigger.'),
                        Forms\Components\Select::make('featured_product_id')
                            ->label('Featured Product (Left Side)')
                            ->relationship('featuredProduct', 'name')
                            ->getOptionLabelFromRecordUsing(fn(Product $record) => "{$record->name} ({$record->sku})")
                            ->searchable()
                            ->preload(),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),

                Forms\Components\Section::make('Mega Menu Layout (Right Side)')
                    ->schema([
                        Forms\Components\Repeater::make('columns')
                            ->label('Columns')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Column Title')
                                    ->required()
                                    ->placeholder('e.g., NGÀNH HÀNG'),

                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('load_categories')
                                        ->label('Auto-fill Categories')
                                        ->color('success')
                                        ->icon('heroicon-m-bolt')
                                        ->action(function (Forms\Set $set) {
                                            $cats = \App\Models\Category::take(8)->get();
                                            $links = $cats->map(fn($c) => [
                                                'label' => $c->name,
                                                'url' => '/collections/' . $c->slug,
                                                'badge' => ''
                                            ])->toArray();
                                            $set('links', $links);
                                        }),
                                    Forms\Components\Actions\Action::make('load_brands')
                                        ->label('Auto-fill Brands')
                                        ->color('info')
                                        ->icon('heroicon-m-bolt')
                                        ->action(function (Forms\Set $set) {
                                            $brands = \App\Models\Brand::take(8)->get();
                                            $links = $brands->map(fn($b) => [
                                                'label' => $b->name,
                                                'url' => '/search?brand=' . $b->slug,
                                                'badge' => ''
                                            ])->toArray();
                                            $set('links', $links);
                                        }),
                                ]),

                                Forms\Components\Repeater::make('links')
                                    ->label('Menu Links')
                                    ->schema([
                                        Forms\Components\TextInput::make('label')
                                            ->required()
                                            ->placeholder('e.g., Sữa Bột Trẻ Em'),
                                        Forms\Components\TextInput::make('url')
                                            ->required()
                                            ->placeholder('e.g., /collections/sua-bot'),
                                        Forms\Components\TextInput::make('badge')
                                            ->label('Superscript Badge (e.g. quantity)')
                                            ->maxLength(50),
                                    ])
                                    ->columns(3)
                                    ->itemLabel(fn(array $state): ?string => $state['label'] ?? null),
                            ])
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? null),

                        Forms\Components\Repeater::make('bottom_links')
                            ->label('Side Buttons (Left Under Product)')
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->required()
                                    ->placeholder('e.g., BÁN CHẠY'),
                                Forms\Components\TextInput::make('url')
                                    ->placeholder('e.g., /collections/best-selling (or blank)'),
                                Forms\Components\TextInput::make('badge')
                                    ->label('Badge Text (e.g. BEST)'),
                                Forms\Components\Select::make('theme')
                                    ->label('Color Theme')
                                    ->options([
                                        'cyan' => 'Mint Cyan (e.g. BÁN CHẠY)',
                                        'pink' => 'Light Pink (e.g. FLASH SALE)',
                                    ])
                                    ->default('cyan')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['label'] ?? null)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('featuredProduct.name')
                    ->label('Featured Product')
                    ->searchable(),
                Tables\Columns\TextInputColumn::make('sort_order')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
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
            'index' => Pages\ListMegaMenus::route('/'),
            'create' => Pages\CreateMegaMenu::route('/create'),
            'edit' => Pages\EditMegaMenu::route('/{record}/edit'),
        ];
    }
}
