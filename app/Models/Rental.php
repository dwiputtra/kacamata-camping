<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Rental extends Model
{
    public const STATUSES = [
        'pending' => 'Menunggu Konfirmasi',
        'approved' => 'Disetujui',
        'ongoing' => 'Sedang Disewa',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public const STATUS_COLORS = [
        'pending' => 'warning',
        'approved' => 'info',
        'ongoing' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];
    public const BLOCKING_STATUSES = ['pending', 'approved', 'ongoing'];
    protected $fillable = [
        'rental_code',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'pickup_date',
        'return_date',
        'total_days',
        'total_price',
        'status',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'return_date' => 'date',
            'total_days' => 'integer',
            'total_price' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RentalItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUSES[$this->status] ?? $this->status);
    }

    protected function totalPriceFormatted(): Attribute
    {
        return Attribute::get(fn () => 'Rp' . number_format($this->total_price, 0, ',', '.'));
    }

    public static function generateCode(): string
    {
        do {
            $code = 'KC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (static::where('rental_code', $code)->exists());

        return $code;
    }

    /**
     * Selisih hari antara tanggal ambil dan kembali (minimal 1 hari).
     */
    public static function calculateDays(mixed $pickup, mixed $return): int
    {
        $days = Carbon::parse($pickup)->startOfDay()
            ->diffInDays(Carbon::parse($return)->startOfDay());

        return max(1, (int) $days);
    }

    /**
     * Hitung ulang subtotal setiap item, total hari, dan total biaya.
     */
    public function recalculateTotals(): void
    {
        $days = static::calculateDays($this->pickup_date, $this->return_date);

        $this->items()->get()->each(function (RentalItem $item) use ($days) {
            $item->update([
                'subtotal' => $item->quantity * $item->price_per_day * $days,
            ]);
        });

        $this->update([
            'total_days' => $days,
            'total_price' => (int) $this->items()->sum('subtotal'),
        ]);
    }
}