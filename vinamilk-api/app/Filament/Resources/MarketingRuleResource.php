<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingRuleResource\Pages;
use App\Filament\Resources\MarketingRuleResource\RelationManagers;
use App\Models\MarketingRule;
use App\Services\MarketingEngine\RuleLoader;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MarketingRuleResource extends Resource
{
    protected static ?string $model = MarketingRule::class;

    protected static ?string $navigationIcon  = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $modelLabel      = 'Luật khuyến mãi';
    protected static ?string $pluralModelLabel = 'Promotion Engine';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── SECTION 1: Basic Info ──────────────────────────────────────
            Forms\Components\Section::make('Thông tin chung')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Tên luật')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),

                    Forms\Components\Textarea::make('description')
                        ->label('Mô tả nội bộ')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Kích hoạt')
                        ->default(true)
                        ->inline(false),

                    Forms\Components\DateTimePicker::make('start_date')
                        ->label('Ngày bắt đầu')
                        ->nullable(),

                    Forms\Components\DateTimePicker::make('end_date')
                        ->label('Ngày kết thúc')
                        ->nullable()
                        ->after('start_date'),
                ])->columns(3),

            // ── SECTION 2: Conflict & Priority ────────────────────────────
            Forms\Components\Section::make('Ưu tiên & Xung đột')
                ->schema([
                    Forms\Components\TextInput::make('priority')
                        ->label('Độ ưu tiên')
                        ->helperText('Số nhỏ hơn = ưu tiên cao hơn')
                        ->numeric()
                        ->default(100)
                        ->required(),

                    Forms\Components\Toggle::make('is_stackable')
                        ->label('Stackable')
                        ->helperText('Bật = cho phép áp dụng cùng các rule khác')
                        ->default(false)
                        ->inline(false),

                    Forms\Components\TextInput::make('exclusive_group')
                        ->label('Nhóm độc quyền')
                        ->helperText('Cùng group → chỉ 1 rule được áp dụng')
                        ->nullable()
                        ->maxLength(100),

                    Forms\Components\Select::make('condition_logic')
                        ->label('Logic điều kiện')
                        ->options(['AND' => 'AND (tất cả phải đúng)', 'OR' => 'OR (một trong số)'])
                        ->default('AND')
                        ->required(),
                ])->columns(4),

            // ── SECTION 3: Usage Limits ────────────────────────────────────
            Forms\Components\Section::make('Giới hạn sử dụng')
                ->schema([
                    Forms\Components\TextInput::make('usage_limit')
                        ->label('Tổng số lần sử dụng')
                        ->helperText('Để trống = không giới hạn')
                        ->numeric()
                        ->nullable(),

                    Forms\Components\TextInput::make('per_user_limit')
                        ->label('Giới hạn mỗi khách')
                        ->helperText('Để trống = không giới hạn')
                        ->numeric()
                        ->nullable(),

                    Forms\Components\Placeholder::make('usage_count')
                        ->label('Đã sử dụng')
                        ->content(fn(?MarketingRule $record) => $record?->usage_count ?? 0),
                ])->columns(3),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('priority')
                    ->label('Ưu tiên')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->width('70px'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Tên luật')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Kích hoạt')
                    ->afterStateUpdated(fn() => RuleLoader::invalidateCache()),

                Tables\Columns\IconColumn::make('is_stackable')
                    ->label('Stackable')
                    ->boolean(),

                Tables\Columns\TextColumn::make('conditions_count')
                    ->label('Điều kiện')
                    ->counts('conditions')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('rewards_count')
                    ->label('Phần thưởng')
                    ->counts('rewards')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Đã dùng')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Bắt đầu')
                    ->dateTime('d/m/Y')
                    ->placeholder('Không giới hạn'),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Kết thúc')
                    ->dateTime('d/m/Y')
                    ->placeholder('Không giới hạn'),
            ])
            ->defaultSort('priority')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái'),
                Tables\Filters\TernaryFilter::make('is_stackable')
                    ->label('Stackable'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(fn() => RuleLoader::invalidateCache()),
                Tables\Actions\DeleteAction::make()
                    ->after(fn() => RuleLoader::invalidateCache()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(fn() => RuleLoader::invalidateCache()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ConditionsRelationManager::class,
            RelationManagers\RewardsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMarketingRules::route('/'),
            'create' => Pages\CreateMarketingRule::route('/create'),
            'edit'   => Pages\EditMarketingRule::route('/{record}/edit'),
        ];
    }
}
