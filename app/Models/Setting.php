<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings'));
        static::deleted(fn () => Cache::forget('settings'));
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $settings = Cache::rememberForever('settings', fn () => static::pluck('value', 'key')->all());

        return $settings[$key] ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Semua pengaturan website, dengan nilai bawaan jika belum diisi admin.
     */
    public static function site(): array
    {
        $defaults = [
            'site_name' => config('app.name'),
            'tagline' => 'Sewa alat camping lengkap, terawat, dan harga bersahabat.',
            'logo' => null,
            'whatsapp' => '6281234567890',
            'email' => 'halo@kacamatacamping.test',
            'address' => 'Alamat toko belum diatur.',
            'maps_embed' => null,
            'instagram' => null,
            'facebook' => null,
            'tiktok' => null,
                'payment_info' => 'Informasi rekening pembayaran akan dikirim admin lewat WhatsApp setelah pesanan disetujui.',
        ];

        $stored = Cache::rememberForever('settings', fn () => static::pluck('value', 'key')->all());

        $site = array_merge($defaults, array_filter($stored, fn ($value) => filled($value)));

        $site['whatsapp_number'] = static::normalizePhone($site['whatsapp']);
        $site['logo_url'] = $site['logo'] ? Storage::disk('public')->url($site['logo']) : null;

        return $site;
    }

    /**
     * Ubah 0812... atau +62812... menjadi 62812... (format wa.me).
     */
    public static function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }
}