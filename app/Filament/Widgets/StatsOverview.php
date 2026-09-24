<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $revenue = (int) Payment::where('status', 'paid')->sum('amount');

        $totalUnits = (int) Product::where('is_active', true)->sum('stock');

        $rentedUnits = (int) RentalItem::whereHas(
            'rental',
            fn ($query) => $query->where('status', 'ongoing')
        )->sum('quantity');

        $pending = Rental::where('status', 'pending')->count();

        return [
            Stat::make('Total Produk', Product::count())
                ->description('Jenis alat di katalog')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make('Total Penyewaan', Rental::count())
                ->description($pending . ' menunggu konfirmasi')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            Stat::make('Total Pelanggan', User::where('role', User::ROLE_CUSTOMER)->count())
                ->description('Akun terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Pendapatan', 'Rp' . number_format($revenue, 0, ',', '.'))
                ->description('Dari pembayaran lunas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Produk Tersedia', max(0, $totalUnits - $rentedUnits))
                ->description('Unit siap disewa')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Produk Disewa', $rentedUnits)
                ->description('Unit sedang dipakai')
                ->descriptionIcon('heroicon-m-truck')
                ->color('gray'),
        ];
    }
}