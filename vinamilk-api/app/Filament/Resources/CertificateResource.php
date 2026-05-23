<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificateResource\Pages;
use App\Models\Certificate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Certificates';

    protected static ?string $modelLabel = 'Certificate';

    protected static ?string $pluralModelLabel = 'Certificates';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin chứng nhận')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên chứng nhận')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('icon')
                            ->label('Hình ảnh chứng nhận')
                            ->image()
                            ->disk('public')
                            ->directory('certificates')
                            ->required(),
                        Forms\Components\Toggle::make('is_home_featured')
                            ->label('Hiển thị trên Trang chủ')
                            ->default(false),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('icon')
                    ->label('Biểu tượng')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên chứng nhận')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_home_featured')
                    ->label('Trang chủ')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
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
            'index' => Pages\ListCertificates::route('/'),
            'create' => Pages\CreateCertificate::route('/create'),
            'edit' => Pages\EditCertificate::route('/{record}/edit'),
        ];
    }
}
