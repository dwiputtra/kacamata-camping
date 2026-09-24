<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    /** Status pesanan yang sudah boleh menerima bukti pembayaran. */
    private const PAYABLE_STATUSES = ['approved', 'ongoing'];

    private const MAX_PENDING_PAYMENTS = 3;

    public function index(Request $request): View
    {
        $rentals = $request->user()
            ->rentals()
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('rentals'));
    }

    public function show(Request $request, string $code): View
    {
        $rental = $this->findOwned($request, $code);
        $rental->load(['items.product', 'payments']);

        $paid = (int) $rental->payments->where('status', 'paid')->sum('amount');
        $remaining = max(0, $rental->total_price - $paid);

        return view('orders.show', [
            'rental' => $rental,
            'paid' => $paid,
            'remaining' => $remaining,
            'canPay' => in_array($rental->status, self::PAYABLE_STATUSES, true) && $remaining > 0,
            'canCancel' => $rental->status === 'pending',
        ]);
    }

    public function cancel(Request $request, string $code): RedirectResponse
    {
        $rental = $this->findOwned($request, $code);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:300'],
        ], [
            'reason.max' => 'Alasan terlalu panjang (maksimal 300 karakter).',
        ]);

        $note = 'Dibatalkan oleh pelanggan.';

        if (filled($data['reason'] ?? null)) {
            $note .= ' Alasan: ' . $data['reason'];
        }

        // Update bersyarat: hanya berhasil jika status masih "pending" saat ini,
        // sehingga tidak bertabrakan dengan admin yang baru saja menyetujui.
        $updated = Rental::whereKey($rental->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled', 'admin_note' => $note]);

        if (! $updated) {
            return back()->with('error', 'Pesanan ini sudah diproses admin dan tidak bisa dibatalkan sendiri. Silakan hubungi kami lewat WhatsApp.');
        }

        return redirect()
            ->route('orders.show', $rental->rental_code)
            ->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function pay(Request $request, string $code): RedirectResponse
    {
        $rental = $this->findOwned($request, $code);

        if (! in_array($rental->status, self::PAYABLE_STATUSES, true)) {
            return back()->with('error', 'Pembayaran baru bisa dikirim setelah pesanan disetujui admin.');
        }

        $paid = (int) $rental->payments()->where('status', 'paid')->sum('amount');
        $remaining = $rental->total_price - $paid;

        if ($remaining <= 0) {
            return back()->with('error', 'Pesanan ini sudah lunas.');
        }

        if ($rental->payments()->where('status', 'pending')->count() >= self::MAX_PENDING_PAYMENTS) {
            return back()->with('error', 'Masih ada ' . self::MAX_PENDING_PAYMENTS . ' bukti pembayaran yang menunggu verifikasi. Mohon tunggu admin memeriksanya.');
        }

        $data = $request->validate([
            'method' => ['required', Rule::in(['transfer', 'qris'])],
            'amount' => ['required', 'integer', 'min:1', 'max:' . $remaining],
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:300'],
        ], [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa angka bulat.',
            'amount.min' => 'Jumlah pembayaran minimal Rp1.',
            'amount.max' => 'Jumlah melebihi sisa tagihan (Rp' . number_format($remaining, 0, ',', '.') . ').',
            'method.in' => 'Metode pembayaran tidak valid.',
            'proof.image' => 'Bukti pembayaran harus berupa gambar.',
            'proof.mimes' => 'Bukti pembayaran harus berformat JPG, PNG, atau WebP.',
            'proof.max' => 'Ukuran bukti pembayaran maksimal 2 MB.',
            'proof.uploaded' => 'Gagal mengunggah bukti pembayaran. Coba gambar yang lebih kecil.',
            'notes.max' => 'Catatan terlalu panjang (maksimal 300 karakter).',
        ], [
            'method' => 'Metode pembayaran',
            'amount' => 'Jumlah pembayaran',
            'proof' => 'Bukti pembayaran',
            'notes' => 'Catatan',
        ]);

        // Nama file dibuat acak oleh Laravel, bukan memakai nama dari pengguna.
        $path = $request->file('proof')->store('payments', 'public');

        $rental->payments()->create([
            'amount' => $data['amount'],
            'method' => $data['method'],
            'status' => 'pending',
            'proof_path' => $path,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('orders.show', $rental->rental_code)
            ->with('success', 'Bukti pembayaran terkirim. Admin akan memverifikasinya secepatnya.');
    }

    /**
     * Ambil pesanan milik pelanggan yang sedang login.
     * Pesanan orang lain menghasilkan 404, sehingga keberadaannya tidak bocor.
     */
    private function findOwned(Request $request, string $code): Rental
    {
        return $request->user()
            ->rentals()
            ->where('rental_code', $code)
            ->firstOrFail();
    }
}