<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Pengaturan Website';

    protected static ?string $title = 'Pengaturan Website';

    protected static ?string $slug = 'pengaturan-website';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Setting::pluck('value', 'key')->all());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Website')
                    ->schema([
                        Forms\Components\TextInput::make('site_name')
                            ->label('Nama Website')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('tagline')
                            ->label('Slogan Singkat')
                            ->maxLength(255)
                            ->placeholder('Sewa alat camping lengkap, terawat, dan harga bersahabat.'),
                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                            ->disk('public')
                            ->directory('settings')
                            ->visibility('public')
                            ->maxSize(1024)
                            ->helperText('PNG, JPG, atau WebP, maksimal 1 MB. Kosongkan untuk memakai teks nama website.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Kontak')
                    ->schema([
                        Forms\Components\TextInput::make('whatsapp')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->required()
                            ->regex('/^(\+62|62|0)8[0-9]{8,12}$/')
                            ->validationMessages([
                                'regex' => 'Format nomor tidak valid. Contoh: 081234567890.',
                            ])
                            ->placeholder('081234567890'),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('maps_embed')
                            ->label('Kode Embed Google Maps')
                            ->rows(3)
                            ->helperText('Di Google Maps: cari lokasi → Bagikan → Sematkan peta → Salin HTML, lalu tempel di sini. Boleh berupa kode <iframe> lengkap atau hanya URL-nya.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Pembayaran')
                    ->description('Tampil di halaman pesanan pelanggan setelah pesanan disetujui.')
                    ->schema([
                        Forms\Components\Textarea::make('payment_info')
                            ->label('Rekening / QRIS')
                            ->rows(4)
                            ->maxLength(1000)
                            ->placeholder("Transfer BCA 1234567890 a.n. Nama Anda\nAtau scan QRIS di toko."),
                    ]),

                Forms\Components\Section::make('Media Sosial')
                    ->schema([
                        Forms\Components\TextInput::make('instagram')
                            ->label('Instagram')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://instagram.com/akunanda'),
                        Forms\Components\TextInput::make('facebook')
                            ->label('Facebook')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://facebook.com/halamananda'),
                        Forms\Components\TextInput::make('tiktok')
                            ->label('TikTok')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://tiktok.com/@akunanda'),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Jika admin menempel kode <iframe> lengkap, ambil URL dari atribut src.
        $embed = trim((string) ($data['maps_embed'] ?? ''));

        if ($embed !== '' && preg_match('/src=["\']([^"\']+)["\']/i', $embed, $matches)) {
            $embed = html_entity_decode($matches[1]);
        }

        if ($embed !== '' && ! Str::startsWith($embed, 'https://www.google.com/maps')) {
            Notification::make()
                ->title('Kode peta tidak valid')
                ->body('Gunakan kode embed dari Google Maps (Bagikan → Sematkan peta).')
                ->danger()
                ->send();

            return;
        }

        $data['maps_embed'] = $embed;

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = Arr::first($value) ?: null;
            }

            Setting::setValue($key, $value);
        }

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }
}