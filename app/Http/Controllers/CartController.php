<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Cart $cart): View
    {
        return view('cart.index', [
            'lines' => $cart->lines(),
            'perDayTotal' => $cart->perDayTotal(),
        ]);
    }

    public function store(Request $request, Cart $cart): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ], [
            'quantity.min' => 'Jumlah minimal 1.',
            'quantity.max' => 'Jumlah terlalu banyak.',
            'quantity.integer' => 'Jumlah harus berupa angka.',
        ]);

        $product = Product::active()->find($data['product_id']);

        if (! $product || $product->stock < 1) {
            return back()->with('error', 'Produk ini sedang tidak tersedia.');
        }

        $wanted = $cart->quantityOf($product->id) + $data['quantity'];
        $final = min($product->stock, $wanted);

        $cart->set($product->id, $final);

        $message = $final < $wanted
            ? "Jumlah disesuaikan dengan stok ({$product->stock} unit)."
            : "{$product->name} ditambahkan ke keranjang.";

        return redirect()->route('cart.index')->with('success', $message);
    }

    public function update(Request $request, Product $product, Cart $cart): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ], [
            'quantity.required' => 'Jumlah wajib diisi.',
            'quantity.min' => 'Jumlah minimal 1.',
            'quantity.max' => 'Jumlah terlalu banyak.',
            'quantity.integer' => 'Jumlah harus berupa angka.',
        ]);

        if (! $cart->has($product->id)) {
            return back();
        }

        $final = min($data['quantity'], max(1, $product->stock));
        $cart->set($product->id, $final);

        $message = $final < $data['quantity']
            ? "Jumlah disesuaikan dengan stok ({$product->stock} unit)."
            : 'Jumlah diperbarui.';

        return back()->with('success', $message);
    }

    public function destroy(Product $product, Cart $cart): RedirectResponse
    {
        $cart->remove($product->id);

        return back()->with('success', 'Alat dihapus dari keranjang.');
    }
}