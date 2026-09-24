@extends('layouts.app')

@php
    $paymentBadge = [
        'paid' => 'bg-green-100 text-green-800',
        'pending' => 'bg-amber-100 text-amber-800',
        'failed' => 'bg-red-100 text-red-800',
        'refunded' => 'bg-gray-100 text-gray-700',
    ];

    $paymentLabel = [
        'pending' => 'Menunggu Verifikasi',
        'paid' => 'Lunas',
        'failed' => 'Ditolak',
        'refunded' => 'Dikembalikan',
    ];

    $rupiah = fn ($value) => 'Rp' . number_format((int) $value, 0, ',', '.');

    $waText = rawurlencode("Halo, saya ingin menanyakan pesanan {$rental->rental_code} atas nama {$rental->customer_name}.");
@endphp

@section('title', 'Pesanan ' . $rental->rental_code)

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10">

    <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-brand-700 hover:underline">← Semua pesanan</a>

    <div class="mt-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-sm text-gray-500">Kode pesanan</p>
            <h1 class="text-2xl font-extrabold tracking-wide text-gray-900">{{ $rental->rental_code }}</h1>
        </div>
        <span class="mt-1">@include('partials.rental-status', ['rental' => $rental])</span>
    </div>

    @if ($rental->status === 'cancelled' && $rental->admin_note)
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <strong>Catatan pembatalan:</strong> {{ $rental->admin_note }}
        </div>
    @endif

    <div class="mt-6 grid gap-6 md:grid-cols-2">
        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <h2 class="font-semibold text-gray-900">Data Penyewa</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div><dt class="text-gray-500">Nama</dt><dd class="font-medium text-gray-900">{{ $rental->customer_name }}</dd></div>
                <div><dt class="text-gray-500">WhatsApp</dt><dd class="font-medium text-gray-900">{{ $rental->customer_phone }}</dd></div>
                <div><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $rental->customer_email }}</dd></div>
                <div><dt class="text-gray-500">Alamat</dt><dd class="font-medium text-gray-900">{!! nl2br(e($rental->customer_address)) !!}</dd></div>
            </dl>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <h2 class="font-semibold text-gray-900">Jadwal Sewa</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div><dt class="text-gray-500">Tanggal ambil</dt><dd class="font-medium text-gray-900">{{ $rental->pickup_date->translatedFormat('l, d F Y') }}</dd></div>
                <div><dt class="text-gray-500">Tanggal kembali</dt><dd class="font-medium text-gray-900">{{ $rental->return_date->translatedFormat('l, d F Y') }}</dd></div>
                <div><dt class="text-gray-500">Lama sewa</dt><dd class="font-medium text-gray-900">{{ $rental->total_days }} hari</dd></div>
            </dl>
        </section>
    </div>

    <section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        <h2 class="px-6 pt-6 font-semibold text-gray-900">Alat yang Disewa</h2>

        <div class="overflow-x-auto">
            <table class="mt-3 w-full text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Alat</th>
                        <th class="px-3 py-3 text-right">Jumlah</th>
                        <th class="px-3 py-3 text-right">Harga/Hari</th>
                        <th class="px-6 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($rental->items as $item)
                        <tr>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $item->product->name }}</td>
                            <td class="px-3 py-3 text-right">{{ $item->quantity }}</td>
                            <td class="px-3 py-3 text-right">{{ $rupiah($item->price_per_day) }}</td>
                            <td class="px-6 py-3 text-right">{{ $rupiah($item->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-gray-200">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right font-semibold text-gray-900">Total ({{ $rental->total_days }} hari)</td>
                        <td class="px-6 py-4 text-right text-base font-extrabold text-brand-700">{{ $rental->total_price_formatted }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>

    {{-- ================= PEMBAYARAN ================= --}}
    @if ($rental->status !== 'cancelled')
    <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <h2 class="font-semibold text-gray-900">Pembayaran</h2>

        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
            <div>
                <dt class="text-gray-500">Total tagihan</dt>
                <dd class="font-semibold text-gray-900">{{ $rental->total_price_formatted }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Sudah dibayar (terverifikasi)</dt>
                <dd class="font-semibold text-green-700">{{ $rupiah($paid) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Sisa tagihan</dt>
                <dd class="font-semibold {{ $remaining > 0 ? 'text-amber-700' : 'text-green-700' }}">{{ $rupiah($remaining) }}</dd>
            </div>
        </dl>

        @if ($rental->payments->isNotEmpty())
            <ul class="mt-4 divide-y divide-gray-100 border-t border-gray-100 text-sm">
                @foreach ($rental->payments as $payment)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-3">
                        <div>
                            <p class="font-medium text-gray-900">
                                {{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }} · {{ $rupiah($payment->amount) }}
                            </p>
                            <p class="text-xs text-gray-500">Dikirim {{ $payment->created_at->translatedFormat('d M Y, H:i') }}</p>
                        </div>

                        <div class="flex items-center gap-3">
                            @if ($payment->proof_path)
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($payment->proof_path) }}"
                                   target="_blank" rel="noopener noreferrer"
                                   class="text-xs font-semibold text-brand-700 hover:underline">Lihat bukti</a>
                            @endif
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $paymentBadge[$payment->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $paymentLabel[$payment->status] ?? $payment->status }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($canPay)
            <div class="mt-6 rounded-xl bg-brand-50 p-4 text-sm text-brand-900">
                <p class="font-semibold">Cara pembayaran</p>
                <p class="mt-1">{!! nl2br(e($site['payment_info'])) !!}</p>
            </div>

            <form method="POST" action="{{ route('orders.pay', $rental->rental_code) }}" enctype="multipart/form-data"
                  class="mt-6 space-y-4">
                @csrf
                <h3 class="font-semibold text-gray-900">Kirim Bukti Pembayaran</h3>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="method" class="form-label">Metode pembayaran</label>
                        <select id="method" name="method" required class="form-input">
                            <option value="transfer" @selected(old('method') === 'transfer')>Transfer Bank</option>
                            <option value="qris" @selected(old('method') === 'qris')>QRIS</option>
                        </select>
                        @error('method') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="amount" class="form-label">Jumlah dibayar (Rp)</label>
                        <input id="amount" name="amount" type="number" min="1" max="{{ $remaining }}" required
                               value="{{ old('amount', $remaining) }}" class="form-input">
                        @error('amount') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="proof" class="form-label">Bukti pembayaran (JPG, PNG, atau WebP, maksimal 2 MB)</label>
                    <input id="proof" name="proof" type="file" required accept="image/png,image/jpeg,image/webp"
                           class="mt-1 block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                    @error('proof') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="notes" class="form-label">Catatan (opsional)</label>
                    <input id="notes" name="notes" type="text" maxlength="300" value="{{ old('notes') }}" class="form-input">
                    @error('notes') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn-primary">Kirim Bukti Pembayaran</button>
            </form>
        @elseif ($rental->status === 'pending')
            <p class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Pembayaran dibuka setelah pesanan disetujui admin. Kami akan mengonfirmasi lewat WhatsApp.
            </p>
        @elseif ($remaining <= 0 && $rental->total_price > 0)
            <p class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">Pesanan ini sudah lunas. Terima kasih!</p>
        @endif
    </section>
    @endif

    {{-- ================= AKSI ================= --}}
    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
        <a href="https://wa.me/{{ $site['whatsapp_number'] }}?text={{ $waText }}" target="_blank" rel="noopener noreferrer"
           class="btn-primary py-3">Hubungi via WhatsApp</a>
        <a href="{{ route('catalog.index') }}" class="btn-outline py-3">Sewa Alat Lain</a>
    </div>

    @if ($canCancel)
        <details class="mt-8 rounded-xl bg-white ring-1 ring-red-200">
            <summary class="cursor-pointer list-none px-5 py-4 text-sm font-semibold text-red-700">Ingin membatalkan pesanan ini?</summary>

            <form method="POST" action="{{ route('orders.cancel', $rental->rental_code) }}"
                  onsubmit="return confirm('Batalkan pesanan ini? Tindakan ini tidak bisa diurungkan.')"
                  class="space-y-3 px-5 pb-5">
                @csrf
                <div>
                    <label for="reason" class="form-label">Alasan (opsional)</label>
                    <input id="reason" name="reason" type="text" maxlength="300" value="{{ old('reason') }}" class="form-input">
                    @error('reason') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                    Ya, Batalkan Pesanan
                </button>
            </form>
        </details>
    @elseif (in_array($rental->status, ['approved', 'ongoing'], true))
        <p class="mt-8 text-center text-xs text-gray-500">
            Pesanan yang sudah disetujui hanya bisa dibatalkan lewat admin. Silakan hubungi kami via WhatsApp.
        </p>
    @endif
</div>
@endsection