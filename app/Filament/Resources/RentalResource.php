<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentalResource\Pages;
use App\Filament\Resources\RentalResource\RelationManagers;
use App\Models\Product;
use App\Models\Rental;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RentalResource extends Resource
{
    protected static ?string $model = Rental::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Penyewaan';

    protected static ?string $pluralModelLabel = 'Penyewaan';

    protected static ?string $recordTitleAttribute = 'rental_code';

    public static function getNavigationBadge(): ?string
    {
        $count = Rental::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount([
            'payments as pending_payments_count' => fn (Builder $query) => $query->where('status', 'pending'),
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Penyewa')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Akun Pelanggan')
                            ->relationship('user', 'name', fn (Builder $query) => $query->where('role', User::ROLE_CUSTOMER))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $user = $state ? User::find($state) : null;

                                if ($user) {
                                    $set('customer_name', $user->name);
                                    $set('customer_email', $user->email);
                                    $set('customer_phone', $user->phone);
                                    $set('customer_address', $user->address);
                                }
                            })
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('customer_phone')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->required()
                            ->regex('/^(\+62|62|0)8[0-9]{8,12}$/')
                            ->validationMessages([
                                'regex' => 'Format nomor tidak valid. Contoh: 081234567890.',
                            ]),
                        Forms\Components\TextInput::make('customer_email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('customer_address')
                            ->label('Alamat')
                            ->required()
                            ->rows(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Jadwal & Status')
                    ->schema([
                        Forms\Components\DatePicker::make('pickup_date')
                            ->label('Tanggal Ambil')
                            ->required(),
                        Forms\Components\DatePicker::make('return_date')
                            ->label('Tanggal Kembali')
                            ->required()
                            ->afterOrEqual('pickup_date'),
                        Forms\Components\Select::make('status')
                            ->label('Status Pesanan')
                            ->options(Rental::STATUSES)
                            ->default('pending')
                            ->required(),
                        Forms\Components\TextInput::make('total_days')
                            ->label('Total Hari')
                            ->suffix('hari')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                        Forms\Components\TextInput::make('total_price')
                            ->label('Total Biaya')
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($state) => number_format((int) $state, 0, ',', '.'))
                            ->visibleOn('edit'),
                        Forms\Components\Textarea::make('admin_note')
                            ->label('Catatan Admin')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Alat yang Disewa')
                    ->description('Total biaya dihitung otomatis saat disimpan: Jumlah × Harga/Hari × Total Hari.')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Daftar Alat')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $set('price_per_day', Product::find($state)?->price_per_day ?? 0);
                                    })
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(),
                                Forms\Components\TextInput::make('price_per_day')
                                    ->label('Harga / Hari')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->required(),
                                Forms\Components\Hidden::make('subtotal')
                                    ->default(0),
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Alat'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rental_code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Penyewa')
                    ->searchable()
                    ->description(fn (Rental $record) => $record->customer_phone),
                Tables\Columns\TextColumn::make('pickup_date')
                    ->label('Ambil')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_date')
                    ->label('Kembali')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format((int) $state, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Rental::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => Rental::STATUS_COLORS[$state] ?? 'gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pending_payments_count')
                    ->label('Bukti Bayar')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state . ' menunggu' : '-')
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(Rental::STATUSES),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    static::statusAction('approve', 'Setujui', 'heroicon-o-check-circle', 'success', 'pending', 'approved', 'Pesanan disetujui.'),
                    static::statusAction('start', 'Mulai Sewa', 'heroicon-o-truck', 'info', 'approved', 'ongoing', 'Status diubah menjadi Sedang Disewa.'),
                    static::statusAction('complete', 'Tandai Selesai', 'heroicon-o-flag', 'success', 'ongoing', 'completed', 'Penyewaan ditandai selesai.'),
                    Tables\Actions\Action::make('reject')
                        ->label('Tolak / Batalkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Rental $record) => in_array($record->status, ['pending', 'approved']))
                        ->form([
                            Forms\Components\Textarea::make('admin_note')
                                ->label('Alasan')
                                ->required()
                                ->maxLength(500),
                        ])
                        ->action(function (Rental $record, array $data) {
                            $record->update([
                                'status' => 'cancelled',
                                'admin_note' => $data['admin_note'],
                            ]);

                            Notification::make()->title('Pesanan dibatalkan.')->success()->send();
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ]);
    }

    protected static function statusAction(
        string $name,
        string $label,
        string $icon,
        string $color,
        string $from,
        string $to,
        string $message,
    ): Tables\Actions\Action {
        return Tables\Actions\Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->visible(fn (Rental $record) => $record->status === $from)
            ->requiresConfirmation()
            ->action(function (Rental $record) use ($to, $message) {
                $record->update(['status' => $to]);

                Notification::make()->title($message)->success()->send();
            });
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRentals::route('/'),
            'create' => Pages\CreateRental::route('/create'),
            'edit' => Pages\EditRental::route('/{record}/edit'),
        ];
    }
}