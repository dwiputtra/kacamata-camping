<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const STATUSES = [
        'pending' => 'Menunggu Pembayaran',
        'paid' => 'Lunas',
        'failed' => 'Gagal',
        'refunded' => 'Dikembalikan',
    ];

    public const METHODS = [
        'transfer' => 'Transfer Bank',
        'qris' => 'QRIS',
        'cash' => 'Tunai (bayar di tempat)',
    ];

    protected $fillable = [
        'rental_id',
        'amount',
        'method',
        'status',
        'proof_path',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}