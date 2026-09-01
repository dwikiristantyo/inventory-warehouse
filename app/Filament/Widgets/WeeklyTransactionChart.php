<?php

namespace App\Filament\Widgets;

use App\Models\TransactionHeader;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class WeeklyTransactionChart extends ChartWidget
{
    protected ?string $heading = 'Statistik Transaksi Barang Masuk & Keluar (Mingguan)';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $days = [];
        $goodsInData = [];
        $goodsOutData = [];

        // Looping dari hari Senin sampai Minggu minggu ini
        for ($date = $startOfWeek->clone(); $date->lte($endOfWeek); $date->addDay()) {
            $formattedDate = $date->format('Y-m-d');
            
            // Label nama hari (Sen, Sel, Rab, dst)
            $days[] = $date->translatedFormat('D');

            // Hitung total transaksi per hari
            $goodsInData[] = TransactionHeader::where('trans_type', 'IN')
                ->whereDate('trans_date', $formattedDate)
                ->count();

            $goodsOutData[] = TransactionHeader::where('trans_type', 'OUT')
                ->whereDate('trans_date', $formattedDate)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Barang Masuk',
                    'data' => $goodsInData,
                    'backgroundColor' => '#3b82f6',
                ],
                [
                    'label' => 'Barang Keluar',
                    'data' => $goodsOutData,
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => $days,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}