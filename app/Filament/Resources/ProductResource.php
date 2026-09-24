<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Katalog';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Produk';

    protected static ?string $pluralModelLabel = 'Produk';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informasi Produk')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Produk')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, ?string $state, Set $set) {
                                        if ($operation === 'create') {
                                            $set('slug', Str::slug((string) $state));
                                        }
                                    }),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Select::make('category_id')
                                    ->label('Kategori')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Spesifikasi')
                            ->schema([
                                Forms\Components\KeyValue::make('specifications')
                                    ->label('Daftar Spesifikasi')
                                    ->keyLabel('Nama')
                                    ->valueLabel('Isi')
                                    ->keyPlaceholder('Kapasitas')
                                    ->valuePlaceholder('4 orang')
                                    ->addActionLabel('Tambah Spesifikasi')
                                    ->reorderable(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Harga & Stok')
                            ->schema([
                                Forms\Components\TextInput::make('price_per_day')
                                    ->label('Harga Sewa per Hari')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->required(),
                                Forms\Components\TextInput::make('stock')
                                    ->label('Stok')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(1)
                                    ->required(),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Tampilkan di katalog')
                                    ->default(true),
                            ]),

                        Forms\Components\Section::make('Foto Utama')
                            ->schema([
                                Forms\Components\FileUpload::make('thumbnail')
                                    ->label('Foto Utama')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->disk('public')
                                    ->directory('products')
                                    ->visibility('public')
                                    ->maxSize(2048)
                                    ->helperText('JPG, PNG, atau WebP. Maksimal 2 MB.'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),

                Forms\Components\Section::make('Galeri Foto')
                    ->description('Foto tambahan yang tampil di halaman detail produk.')
                    ->schema([
                        Forms\Components\Repeater::make('images')
                            ->label('Foto Galeri')
                            ->relationship()
                            ->schema([
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('Gambar')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->disk('public')
                                    ->directory('products/gallery')
                                    ->visibility('public')
                                    ->maxSize(2048)
                                    ->required(),
                            ])
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->grid(3)
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Gambar'),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Foto')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_day')
                    ->label('Harga / Hari')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format((int) $state, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Product $record) {
                        if ($record->rentalItems()->exists()) {
                            Notification::make()
                                ->title('Produk tidak bisa dihapus')
                                ->body('Produk ini sudah pernah disewa. Matikan saja opsi "Tampilkan di katalog" agar riwayat tetap utuh.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}