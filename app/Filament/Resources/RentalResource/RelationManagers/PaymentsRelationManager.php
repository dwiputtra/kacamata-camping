<?php

namespace App\Filament\Resources\RentalResource\RelationManagers;

use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Pembayaran';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah Dibayar')
                    ->numeric()
                    ->minValue(1)
                    ->prefix('Rp')
                    ->required()
                    ->default(fn () => $this->getOwnerRecord()->total_price),
                Forms\Components\Select::make('method')
                    ->label('Metode')
                    ->options(Payment::METHODS)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status Pembayaran')
                    ->options(Payment::STATUSES)
                    ->default('pending')
                    ->required(),
                Forms\Components\DateTimePicker::make('paid_at')
                    ->label('Waktu Dibayar')
                    ->seconds(false),
                Forms\Components\FileUpload::make('proof_path')
                    ->label('Bukti Pembayaran')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->disk('public')
                    ->directory('payments')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('method')
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format((int) $state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('method')
                    ->label('Metode')
                    ->formatStateUsing(fn ($state) => Payment::METHODS[$state] ?? $state),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Payment::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Dibayar')
                    ->dateTime('d M Y H:i'),
                Tables\Columns\ImageColumn::make('proof_path')
                    ->label('Bukti')
                    ->disk('public'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(fn (array $data) => static::fillPaidAt($data)),
            ])
            ->actions([
                                Tables\Actions\Action::make('verify')
                    ->label('Tandai Lunas')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Payment $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalDescription('Pastikan dana sudah benar-benar masuk sebelum menandai lunas.')
                    ->action(fn (Payment $record) => $record->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ])),
                    
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(fn (array $data) => static::fillPaidAt($data)),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected static function fillPaidAt(array $data): array
    {
        if (($data['status'] ?? null) === 'paid' && empty($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        return $data;
    }
}