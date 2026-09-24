<?php

namespace App\Filament\Widgets;

use App\Models\Rental;
use Filament\Widgets\ChartWidget;

class RentalStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Status Penyewaan';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $counts = Rental::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $data = collect(array_keys(Rental::STATUSES))
            ->map(fn (string $status) => (int) ($counts[$status] ?? 0))
            ->all();

        return [
            'datasets' => [
                [
                    'data' => $data,
                    // urutan: menunggu, disetujui, sedang disewa, selesai, dibatalkan
                    'backgroundColor' => ['#f59e0b', '#0ea5e9', '#8b5cf6', '#22c55e', '#ef4444'],
                ],
            ],
            'labels' => array_values(Rental::STATUSES),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }
}