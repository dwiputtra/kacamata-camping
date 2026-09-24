<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\RentalResource;
use App\Models\Rental;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestRentals extends BaseWidget
{
    protected static ?string $heading = 'Penyewaan Terbaru';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Rental::query()->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('rental_code')
                    ->label('Kode'),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Penyewa'),
                Tables\Columns\TextColumn::make('pickup_date')
                    ->label('Ambil')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format((int) $state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Rental::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => Rental::STATUS_COLORS[$state] ?? 'gray'),
            ])
            ->paginated(false)
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Buka')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Rental $record) => RentalResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}