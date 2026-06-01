<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentLogResource\Pages;
use App\Models\PaymentLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentLogResource extends Resource
{
    protected static ?string $model = PaymentLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Bán hàng';

    protected static ?string $navigationLabel = 'Lịch sử Thanh toán';

    protected static ?int $navigationSort = 4;

    protected static ?string $pluralModelLabel = 'Lịch sử Sandbox & COD';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Section::make('Thông tin chung')
                            ->schema([
                                Forms\Components\TextInput::make('order_number')
                                    ->readOnly()
                                    ->label('Mã đơn hàng'),
                                Forms\Components\TextInput::make('payment_method')
                                    ->readOnly()
                                    ->label('Phương thức thanh toán')
                                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                                Forms\Components\Placeholder::make('amount_display')
                                    ->label('Số tiền')
                                    ->content(fn ($record) => $record ? number_format($record->amount) . 'đ' : '0đ'),
                                Forms\Components\Placeholder::make('status_display')
                                    ->label('Trạng thái giao dịch')
                                    ->content(fn ($record) => match ($record?->status) {
                                        'pending' => new \Illuminate\Support\HtmlString('<span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-bold uppercase">Đang xử lý / Chờ COD</span>'),
                                        'success' => new \Illuminate\Support\HtmlString('<span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold uppercase">Thành công</span>'),
                                        'failed' => new \Illuminate\Support\HtmlString('<span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold uppercase">Lỗi thanh toán</span>'),
                                        'cancelled' => new \Illuminate\Support\HtmlString('<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-bold uppercase">Đã hủy</span>'),
                                        default => $record?->status,
                                    }),
                            ])->columnSpan(1),

                        Forms\Components\Section::make('Chi tiết kỹ thuật (Payloads)')
                            ->schema([
                                Forms\Components\Placeholder::make('request_payload_display')
                                    ->label('Dữ liệu gửi lên server (Request Payload)')
                                    ->content(fn ($record) => $record && $record->request_payload 
                                        ? new \Illuminate\Support\HtmlString("<pre class='p-4 bg-gray-900 text-green-400 rounded-lg overflow-auto max-h-96 text-xs font-mono shadow-inner'>" . json_encode($record->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>")
                                        : '-'
                                    ),
                                Forms\Components\Placeholder::make('response_payload_display')
                                    ->label('Dữ liệu phản hồi từ Gateway (Response/Callback Payload)')
                                    ->content(fn ($record) => $record && $record->response_payload 
                                        ? new \Illuminate\Support\HtmlString("<pre class='p-4 bg-gray-900 text-blue-400 rounded-lg overflow-auto max-h-96 text-xs font-mono shadow-inner'>" . json_encode($record->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>")
                                        : new \Illuminate\Support\HtmlString("<span class='text-gray-400 italic'>Chưa nhận được callback phản hồi từ cổng thanh toán (hoặc thanh toán COD chưa được xác nhận)</span>")
                                    ),
                            ])->columnSpan(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable()
                    ->label('Mã đơn hàng')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable()
                    ->sortable()
                    ->label('Phương thức')
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                Tables\Columns\TextColumn::make('amount')
                    ->money('VND')
                    ->sortable()
                    ->label('Số tiền'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'success',
                        'danger' => 'failed',
                        'secondary' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'ĐANG XỬ LÝ',
                        'success' => 'THÀNH CÔNG',
                        'failed' => 'LỖI',
                        'cancelled' => 'HỦY',
                        default => strtoupper($state),
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Thời gian khởi tạo'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Đang xử lý',
                        'success' => 'Thành công',
                        'failed' => 'Lỗi',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Xem chi tiết'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentLogs::route('/'),
        ];
    }
}
