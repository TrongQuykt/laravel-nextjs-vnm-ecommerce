<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VatOrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VatOrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Quản lý VAT Đơn hàng';

    protected static ?string $pluralModelLabel = 'Quản lý VAT Đơn hàng';

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Section::make('Thông tin đơn hàng')
                            ->schema([
                                Forms\Components\TextInput::make('order_number')
                                    ->readOnly()
                                    ->label('Mã đơn hàng'),
                                Forms\Components\Placeholder::make('total_amount_display')
                                    ->label('Tổng tiền hàng')
                                    ->content(fn ($record) => $record ? number_format($record->total_amount) . 'đ' : '0đ'),
                                Forms\Components\TextInput::make('payment_method')
                                    ->readOnly()
                                    ->label('Phương thức thanh toán')
                                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                                Forms\Components\TextInput::make('payment_status')
                                    ->readOnly()
                                    ->label('Trạng thái thanh toán')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'paid' => 'ĐÃ THANH TOÁN',
                                        'unpaid' => 'CHƯA THANH TOÁN',
                                        'pending_payment' => 'CHỜ XÁC NHẬN',
                                        default => strtoupper($state),
                                    }),
                            ])->columnSpan(1),

                        Forms\Components\Section::make('Thông tin xuất hóa đơn VAT')
                            ->schema([
                                Forms\Components\TextInput::make('invoice_info.type')
                                    ->readOnly()
                                    ->label('Phân loại')
                                    ->formatStateUsing(fn ($state) => $state === 'personal' ? 'CÁ NHÂN' : 'CÔNG TY / ĐƠN VỊ'),
                                Forms\Components\TextInput::make('invoice_info.name')
                                    ->readOnly()
                                    ->label('Họ tên / Tên đơn vị'),
                                Forms\Components\TextInput::make('invoice_info.tax_code')
                                    ->readOnly()
                                    ->label('Mã số thuế (MST)'),
                                Forms\Components\Textarea::make('invoice_info.address')
                                    ->readOnly()
                                    ->label('Địa chỉ'),
                                Forms\Components\TextInput::make('invoice_info.phone')
                                    ->readOnly()
                                    ->label('Số điện thoại'),
                                Forms\Components\TextInput::make('invoice_info.email')
                                    ->readOnly()
                                    ->label('Email nhận hóa đơn điện tử'),
                            ])->columnSpan(1),

                        Forms\Components\Section::make('Chi tiết thuế suất & phân rã VAT từng sản phẩm')
                            ->schema([
                                Forms\Components\Placeholder::make('vat_breakdown_table')
                                    ->label('')
                                    ->content(function ($record) {
                                        if (!$record) return 'Không có sản phẩm nào.';
                                        
                                        $items = $record->items;
                                        if ($items->isEmpty()) return 'Đơn hàng không có sản phẩm.';
                                        
                                        $html = '
                                        <div class="overflow-x-auto border border-gray-100 rounded-lg">
                                            <table class="w-full text-left border-collapse text-xs">
                                                <thead>
                                                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold">
                                                        <th class="p-3">Tên sản phẩm</th>
                                                        <th class="p-3 text-center">Số lượng</th>
                                                        <th class="p-3 text-right">Đơn giá (gồm thuế)</th>
                                                        <th class="p-3 text-right">Thành tiền (gồm thuế)</th>
                                                        <th class="p-3 text-center">Thuế suất VAT</th>
                                                        <th class="p-3 text-right">Tiền trước thuế (Net)</th>
                                                        <th class="p-3 text-right">Tiền thuế VAT</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-50 font-medium text-gray-700">';
                                                
                                        $totalNet = 0;
                                        $totalVat = 0;
                                        $totalGross = 0;
                                        
                                        foreach ($items as $item) {
                                            $gross = (float)$item->total;
                                            $rate = 0.10; 
                                            $rateLabel = '10%';
                                            
                                            $lowerName = mb_strtolower($item->product_name);
                                            if (str_contains($lowerName, 'sữa tươi') || str_contains($lowerName, 'sữa đặc') || str_contains($lowerName, 'sữa chua') || str_contains($lowerName, 'tiệt trùng') || str_contains($lowerName, 'thanh trùng')) {
                                                $rate = 0.08;
                                                $rateLabel = '8%';
                                            }
                                            
                                            $net = $gross / (1 + $rate);
                                            $vat = $gross - $net;
                                            
                                            $totalNet += $net;
                                            $totalVat += $vat;
                                            $totalGross += $gross;
                                            
                                            $html .= '
                                            <tr>
                                                <td class="p-3 font-semibold text-gray-900">' . htmlspecialchars($item->product_name) . ' (' . htmlspecialchars($item->variant_name ?? 'Chuẩn') . ')</td>
                                                <td class="p-3 text-center font-bold text-gray-900">' . number_format($item->quantity) . '</td>
                                                <td class="p-3 text-right">' . number_format((float)$item->price) . 'đ</td>
                                                <td class="p-3 text-right font-bold">' . number_format($gross) . 'đ</td>
                                                <td class="p-3 text-center text-blue-600 font-bold"><span class="px-2 py-0.5 bg-blue-50 border border-blue-100 rounded text-xs">' . $rateLabel . '</span></td>
                                                <td class="p-3 text-right text-gray-500">' . number_format(round($net)) . 'đ</td>
                                                <td class="p-3 text-right text-red-600 font-bold">' . number_format(round($vat)) . 'đ</td>
                                            </tr>';
                                        }
                                        
                                        $html .= '
                                                    <tr class="bg-gray-50/50 font-bold border-t border-gray-100 text-gray-900">
                                                        <td class="p-3">TỔNG CỘNG HÓA ĐƠN</td>
                                                        <td class="p-3 text-center">-</td>
                                                        <td class="p-3 text-right">-</td>
                                                        <td class="p-3 text-right text-blue-900 text-sm">' . number_format($totalGross) . 'đ</td>
                                                        <td class="p-3 text-center">-</td>
                                                        <td class="p-3 text-right text-gray-500">' . number_format(round($totalNet)) . 'đ</td>
                                                        <td class="p-3 text-right text-red-600 text-sm">' . number_format(round($totalVat)) . 'đ</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>';
                                        
                                        return new \Illuminate\Support\HtmlString($html);
                                    })
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
                Tables\Columns\TextColumn::make('invoice_info.type')
                    ->label('Phân loại')
                    ->formatStateUsing(fn ($state) => $state === 'personal' ? 'Cá nhân' : 'Công ty')
                    ->badge()
                    ->color(fn ($state) => $state === 'personal' ? 'info' : 'warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_info.name')
                    ->searchable()
                    ->sortable()
                    ->label('Họ tên / Đơn vị'),
                Tables\Columns\TextColumn::make('invoice_info.tax_code')
                    ->searchable()
                    ->sortable()
                    ->label('Mã số thuế'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('VND')
                    ->sortable()
                    ->label('Tổng tiền'),
                Tables\Columns\TextColumn::make('invoice_info.email')
                    ->searchable()
                    ->label('Email hóa đơn'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Thời gian đặt'),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Từ ngày'),
                        Forms\Components\DatePicker::make('created_until')->label('Đến ngày'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Xem chi tiết'),
                Tables\Actions\Action::make('send_email')
                    ->label('Gửi Email VAT')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn ($record) => !empty($record->invoice_info['email']))
                    ->requiresConfirmation()
                    ->modalHeading('Gửi Email Hóa Đơn VAT')
                    ->modalDescription('Hệ thống sẽ gửi thông báo phát hành hóa đơn điện tử VAT tới email khách hàng.')
                    ->action(function ($record) {
                        try {
                            \Illuminate\Support\Facades\Mail::to($record->invoice_info['email'])->send(new \App\Mail\VatInvoiceMail($record));
                            \Filament\Notifications\Notification::make()
                                ->title('Đã gửi email thành công!')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Lỗi gửi email: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('export_pdf')
                    ->label('Xuất PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->action(function ($record) {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.vat-invoice', ['order' => $record]);
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'Hoa-don-VAT-' . $record->order_number . '.pdf');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export_csv')
                    ->label('Xuất Excel (CSV)')
                    ->icon('heroicon-o-table-cells')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $csvData = [];
                        $csvData[] = ['Ma Don Hang', 'Trang Thai', 'Ngay Dat', 'Ma So Thue', 'Ten Khach Hang/Cong Ty', 'Email', 'Tong Tien Hang', 'Tong Tien VAT', 'Tong Thanh Toan'];
                        foreach ($records as $record) {
                            $totalNet = 0;
                            $totalTax = 0;
                            foreach ($record->items as $item) {
                                $isDairy = preg_match('/sữa tươi|sữa đặc|sữa chua|tiệt trùng|thanh trùng/i', mb_strtolower($item->product_name));
                                $taxRate = $isDairy ? 0.08 : 0.10;
                                $grossTotalLine = $item->total;
                                $netTotalLine = $grossTotalLine / (1 + $taxRate);
                                $taxAmountLine = $grossTotalLine - $netTotalLine;
                                $totalNet += $netTotalLine;
                                $totalTax += $taxAmountLine;
                            }
                            $csvData[] = [
                                $record->order_number,
                                $record->status,
                                $record->created_at->format('Y-m-d H:i:s'),
                                $record->invoice_info['tax_code'] ?? '',
                                $record->invoice_info['company'] ?? ($record->invoice_info['name'] ?? ''),
                                $record->invoice_info['email'] ?? '',
                                round($totalNet),
                                round($totalTax),
                                $record->total_amount
                            ];
                        }
                        
                        return response()->streamDownload(function () use ($csvData) {
                            $file = fopen('php://output', 'w');
                            // Add BOM to fix UTF-8 in Excel
                            fputs($file, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));
                            foreach ($csvData as $row) {
                                fputcsv($file, $row);
                            }
                            fclose($file);
                        }, 'Bao-cao-VAT-' . date('Y-m-d') . '.csv');
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVatOrders::route('/'),
            'view' => Pages\ViewVatOrder::route('/{record}'),
        ];
    }
}
