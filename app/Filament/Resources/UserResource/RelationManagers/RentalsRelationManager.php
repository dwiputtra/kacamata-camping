<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\RentalResource;
use App\Models\Rental;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RentalsRelationManager extends RelationManager
{
    protected static string $relationship = 'rentals';

    protected static ?string $title = 'Riwayat Penyewaan';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('rental_code')
            ->columns([
                Tables\Columns\TextColumn::make('rental_code')
                    ->label('Kode'),
                Tables\Columns\TextColumn::make('pickup_date')
                    ->label('Ambil')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('return_date')
                    ->label('Kembali')
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
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Buka')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Rental $record) => RentalResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}