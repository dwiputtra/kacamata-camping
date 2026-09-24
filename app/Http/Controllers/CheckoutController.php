<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rental;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const MAX_DAYS = 30;

    public function create(Request $request, Cart $cart): View|RedirectResponse
    {
        $lines = $cart->lines();

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        return view('checkout.create', [
            'lines' => $lines,
            'perDayTotal' => $cart->perDayTotal(),
            'user' => $request->user(),
            'maxDays' => self::MAX_DAYS,
        ]);
    }

    public function store(Request $request, Cart $cart): RedirectResponse
    {
        $lines = $cart->lines();

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_address' => ['required', 'string', 'max:1000'],
            'pickup_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:' . now()->addYear()->toDateString(),
            ],
            'return_date' => ['required', 'date', 'after_or_equal:pickup_date'],
        ], [
            'required' => ':attribute wajib diisi.',
            'email' => 'Format :attribute tidak valid.',
            'date' => ':attribute tidak valid.',
            'max' => ':attribute terlalu panjang (maksimal :max karakter).',
            'customer_phone.regex' => 'Nomor WhatsApp tidak valid. Contoh: 081234567890.',
            'pickup_date.after_or_equal' => 'Tanggal ambil tidak boleh sebelum hari ini.',
            'pickup_date.before_or_equal' => 'Tanggal ambil maksimal 1 tahun ke depan.',
            'return_date.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal ambil.',
        ], [
            'customer_name' => 'Nama lengkap',
            'customer_phone' => 'Nomor WhatsApp',
            'customer_email' => 'Email',
            'customer_address' => 'Alamat',
            'pickup_date' => 'Tanggal ambil',
            'return_date' => 'Tanggal kembali',
        ]);

        $days = Rental::calculateDays($data['pickup_date'], $data['return_date']);

        if ($days > self::MAX_DAYS) {
            return back()->withInput()->withErrors([
                'return_date' => 'Lama sewa maksimal ' . self::MAX_DAYS . ' hari.',
            ]);
        }

        $problems = [];

        $rental = DB::transaction(function () use ($request, $data, $lines, $days, &$problems) {
            // Kunci baris produk agar dua pesanan bersamaan tidak bisa memesan stok yang sama.
            $products = Product::whereIn('id', $lines->map(fn ($line) => $line['product']->id)->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($lines as $line) {
                $product = $products->get($line['product']->id);

                $available = ($product && $product->is_active)
                    ? $product->availableQuantity($data['pickup_date'], $data['return_date'])
                    : 0;

                if ($line['quantity'] > $available) {
                    $problems[] = "{$line['product']->name}: tersedia {$available} unit pada tanggal tersebut, Anda meminta {$line['quantity']} unit.";
                }
            }

            if ($problems !== []) {
                return null;
            }

            $rental = Rental::create([
                'rental_code' => Rental::generateCode(),
                'user_id' => $request->user()->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'],
                'customer_address' => $data['customer_address'],
                'pickup_date' => $data['pickup_date'],
                'return_date' => $data['return_date'],
                'total_days' => $days,
                'total_price' => 0,
                'status' => 'pending',
            ]);

            $total = 0;

            foreach ($lines as $line) {
                $product = $products->get($line['product']->id);
                $subtotal = $line['quantity'] * $product->price_per_day * $days;

                $rental->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $line['quantity'],
                    'price_per_day' => $product->price_per_day,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $rental->update(['total_price' => $total]);

            return $rental;
        });

        if (! $rental) {
            return back()->withInput()->with('stock_problems', $problems);
        }

        // Simpan nomor WhatsApp dan alamat ke akun jika sebelumnya masih kosong.
        $user = $request->user();
        $user->fill(array_filter([
            'phone' => blank($user->phone) ? $data['customer_phone'] : null,
            'address' => blank($user->address) ? $data['customer_address'] : null,
        ]))->save();

        $cart->clear();

        return redirect()
            ->route('orders.show', $rental->rental_code)
            ->with('success', 'Pesanan berhasil dibuat! Kami akan segera mengonfirmasi lewat WhatsApp.');
    }
}