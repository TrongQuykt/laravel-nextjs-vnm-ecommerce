<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreResource\Pages;
use App\Filament\Traits\HasRolePermissions;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StoreResource extends Resource
{
    use HasRolePermissions;
    protected static ?string $model = Store::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Cửa hàng & Vận chuyển';

    protected static ?string $modelLabel = 'Cửa hàng';
    protected static ?string $pluralModelLabel = 'Danh sách Cửa hàng';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin chung')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên cửa hàng')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Số điện thoại')
                            ->tel()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Địa chỉ')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->label('Địa chỉ chi tiết (Số nhà, Đường)')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::updateGeocoding($set, $get)),
                        Forms\Components\Select::make('province')
                            ->label('Tỉnh/Thành phố')
                            ->options(function () {
                                return collect(self::getVietnamData())->pluck('name', 'name')->toArray();
                            })
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                $set('district', null);
                                $set('ward', null);
                                self::updateGeocoding($set, $get);
                            }),
                        Forms\Components\Select::make('district')
                            ->label('Quận/Huyện')
                            ->options(function (Forms\Get $get) {
                                $provinceName = $get('province');
                                if (!$provinceName) return [];
                                $data = self::getVietnamData();
                                $province = collect($data)->firstWhere('name', $provinceName);
                                if (!$province) return [];
                                return collect($province['districts'] ?? [])->pluck('name', 'name')->toArray();
                            })
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                $set('ward', null);
                                self::updateGeocoding($set, $get);
                            }),
                        Forms\Components\Select::make('ward')
                            ->label('Phường/Xã')
                            ->options(function (Forms\Get $get) {
                                $provinceName = $get('province');
                                $districtName = $get('district');
                                if (!$provinceName || !$districtName) return [];
                                $data = self::getVietnamData();
                                $province = collect($data)->firstWhere('name', $provinceName);
                                if (!$province) return [];
                                $district = collect($province['districts'] ?? [])->firstWhere('name', $districtName);
                                if (!$district) return [];
                                return collect($district['wards'] ?? [])->pluck('name', 'name')->toArray();
                            })
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::updateGeocoding($set, $get)),
                    ])->columns(3),

                Forms\Components\Section::make('Vị trí cửa hàng')
                    ->description('Bấm vào bản đồ để chọn vị trí chính xác (Lat/Long).')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->label('Vĩ độ (Latitude)')
                                    ->numeric()
                                    ->reactive(),
                                Forms\Components\TextInput::make('longitude')
                                    ->label('Kinh độ (Longitude)')
                                    ->numeric()
                                    ->reactive(),
                            ]),
                        Forms\Components\ViewField::make('map_picker')
                            ->view('filament.components.map-picker')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function updateGeocoding(Forms\Set $set, Forms\Get $get)
    {
        $name = $get('name');
        $address = $get('address');
        $province = $get('province');
        $district = $get('district');
        $ward = $get('ward');

        if (!$address || !$province) return;

        $fullAddress = implode(', ', array_filter([
            $name,
            $address,
            $ward,
            $district,
            $province,
            'Vietnam'
        ]));

        try {
            $apiKey = env('SERPAPI_KEY');
            $response = \Illuminate\Support\Facades\Http::get('https://serpapi.com/search.json', [
                'engine' => 'google_maps',
                'q' => $fullAddress,
                'api_key' => $apiKey,
                'type' => 'search',
                'num' => 1
            ]);
            
            $data = $response->json();
            
            if (isset($data['place_results']['gps_coordinates'])) {
                $set('latitude', $data['place_results']['gps_coordinates']['latitude']);
                $set('longitude', $data['place_results']['gps_coordinates']['longitude']);
            } elseif (isset($data['local_results'][0]['gps_coordinates'])) {
                $set('latitude', $data['local_results'][0]['gps_coordinates']['latitude']);
                $set('longitude', $data['local_results'][0]['gps_coordinates']['longitude']);
            }
        } catch (\Exception $e) {
            // Silently fail in UI
        }
    }

    protected static function getVietnamData()
    {
        return \Illuminate\Support\Facades\Cache::remember('vn_data', 86400, function () {
            return \Illuminate\Support\Facades\Http::get('https://provinces.open-api.vn/api/?depth=3')->json();
        });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên cửa hàng')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('SĐT')
                    ->searchable(),
                Tables\Columns\TextColumn::make('province')
                    ->label('Tỉnh/Thành')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('district')
                    ->label('Quận/Huyện')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('latitude')
                    ->label('Vĩ độ')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('longitude')
                    ->label('Kinh độ')
                    ->toggleable(isToggledHiddenByDefault: true),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStores::route('/'),
            'create' => Pages\CreateStore::route('/create'),
            'edit' => Pages\EditStore::route('/{record}/edit'),
        ];
    }
}
