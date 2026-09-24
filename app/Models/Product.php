<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'price_per_day',
        'stock',
        'thumbnail',
        'description',
        'specifications',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_per_day' => 'integer',
            'stock' => 'integer',
            'specifications' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function rentalItems(): HasMany
    {
        return $this->hasMany(RentalItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    /**
     * Jumlah unit yang sudah terpakai pesanan lain pada rentang tanggal tertentu.
     */
    public function reservedQuantity(mixed $pickup, mixed $return): int
    {
        return (int) $this->rentalItems()
            ->whereHas('rental', function (Builder $query) use ($pickup, $return) {
                $query->whereIn('status', Rental::BLOCKING_STATUSES)
                    ->whereDate('pickup_date', '<=', $return)
                    ->whereDate('return_date', '>=', $pickup);
            })
            ->sum('quantity');
    }

    /**
     * Sisa unit yang bisa disewa pada rentang tanggal tertentu.
     */
    public function availableQuantity(mixed $pickup, mixed $return): int
    {
        return max(0, $this->stock - $this->reservedQuantity($pickup, $return));
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::get(fn () => $this->thumbnail
            ? Storage::disk('public')->url($this->thumbnail)
            : null);
    }

    protected function priceFormatted(): Attribute
    {
        return Attribute::get(fn () => 'Rp' . number_format($this->price_per_day, 0, ',', '.'));
    }
}