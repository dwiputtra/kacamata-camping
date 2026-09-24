<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Pendapatan 6 Bulan Terakhir';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $i) => now()->startOfMonth()->subMonths($i));

        $totals = $months->map(fn (Carbon $month) => (int) Payment::where('status', 'paid')
            ->whereRaw('COALESCE(paid_at, created_at) between ? and ?', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])
            ->sum('amount'));

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan (Rp)',
                    'data' => $totals->all(),
                    'backgroundColor' => '#3f9560',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $months->map(fn (Carbon $month) => $month->translatedFormat('M Y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }
}