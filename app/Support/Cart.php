<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

class Cart
{
    private const KEY = 'cart';

    /**
     * Isi mentah keranjang: [product_id => jumlah].
     *
     * @return array<int, int>
     */
    public function raw(): array
    {
        return session()->get(self::KEY, []);
    }

    public function has(int $productId): bool
    {
        return isset($this->raw()[$productId]);
    }

    public function quantityOf(int $productId): int
    {
        return (int) ($this->raw()[$productId] ?? 0);
    }

    public function set(int $productId, int $quantity): void
    {
        $cart = $this->raw();
        $cart[$productId] = max(1, $quantity);

        session()->put(self::KEY, $cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->raw();
        unset($cart[$productId]);

        session()->put(self::KEY, $cart);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    /**
     * Total unit di keranjang (untuk badge di navbar).
     */
    public function count(): int
    {
        return (int) array_sum($this->raw());
    }

    /**
     * Baris keranjang beserta data produk terbaru dari database.
     * Produk yang sudah dihapus atau dinonaktifkan admin otomatis dilewati.
     */
    public function lines(): Collection
    {
        $raw = $this->raw();

        if ($raw === []) {
            return collect();
        }

        $products = Product::with('category')
            ->whereIn('id', array_keys($raw))
            ->get()
            ->keyBy('id');

        return collect($raw)
            ->map(function ($quantity, $id) use ($products) {
                $product = $products->get($id);

                if (! $product || ! $product->is_active) {
                    return null;
                }

                return [
                    'product' => $product,
                    'quantity' => (int) $quantity,
                    'per_day' => $product->price_per_day * (int) $quantity,
                ];
            })
            ->filter()
            ->values();
    }

    public function perDayTotal(): int
    {
        return (int) $this->lines()->sum('per_day');
    }
}