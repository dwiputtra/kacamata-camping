@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10">
    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Checkout Penyewaan</h1>
    <p class="mt-2 text-gray-600">Lengkapi data dan tanggal sewa. Pesanan akan dikonfirmasi oleh admin.</p>

    @if (session('stock_problems'))
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Beberapa alat tidak cukup tersedia pada tanggal tersebut:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach (session('stock_problems') as $problem)
                    <li>{{ $problem }}</li>
                @endforeach
            </ul>
            <p class="mt-2">Ubah tanggal sewa, atau kurangi jumlah alat di <a href="{{ route('cart.index') }}" class="font-semibold underline">keranjang</a>.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}" class="mt-8 grid gap-6 lg:grid-cols-[1fr_22rem]">
        @csrf

        <div class="space-y-6">
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="font-semibold text-gray-900">Data Penyewa</h2>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="customer_name" class="form-label">Nama lengkap</label>
                        <input id="customer_name" name="customer_name" type="text" required autocomplete="name"
                               value="{{ old('customer_name', $user->name) }}" class="form-input">
                        @error('customer_name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="customer_phone" class="form-label">Nomor WhatsApp</label>
                        <input id="customer_phone" name="customer_phone" type="tel" required autocomplete="tel"
                               value="{{ old('customer_phone', $user->phone) }}" placeholder="081234567890" class="form-input">
                        @error('customer_phone') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="customer_email" class="form-label">Email</label>
                        <input id="customer_email" name="customer_email" type="email" required autocomplete="email"
                               value="{{ old('customer_email', $user->email) }}" class="form-input">
                        @error('customer_email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="customer_address" class="form-label">Alamat</label>
                        <textarea id="customer_address" name="customer_address" rows="3" required
                                  class="form-input">{{ old('customer_address', $user->address) }}</textarea>
                        @error('customer_address') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="font-semibold text-gray-900">Jadwal Sewa</h2>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="pickup_date" class="form-label">Tanggal ambil</label>
                        <input id="pickup_date" name="pickup_date" type="date" required
                               min="{{ now()->toDateString() }}" value="{{ old('pickup_date') }}" class="form-input">
                        @error('pickup_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="return_date" class="form-label">Tanggal kembali</label>
                        <input id="return_date" name="return_date" type="date" required
                               min="{{ now()->toDateString() }}" value="{{ old('return_date') }}" class="form-input">
                        @error('return_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <p class="mt-3 text-xs text-gray-500">
                    Lama sewa dihitung dari selisih tanggal (ambil tanggal 10, kembali tanggal 13 = 3 hari), maksimal {{ $maxDays }} hari.
                    Ambil dan kembali di hari yang sama dihitung 1 hari.
                </p>
            </section>
        </div>

        <aside class="h-fit rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 lg:sticky lg:top-24">
            <h2 class="font-semibold text-gray-900">Ringkasan Pesanan</h2>

            <ul class="mt-4 divide-y divide-gray-100 text-sm">
                @foreach ($lines as $line)
                    <li class="flex justify-between gap-3 py-2">
                        <span class="text-gray-700">{{ $line['product']->name }} × {{ $line['quantity'] }}</span>
                        <span class="shrink-0 text-gray-900">Rp{{ number_format($line['per_day'], 0, ',', '.') }}/hari</span>
                    </li>
                @endforeach
            </ul>

            <dl class="mt-4 space-y-2 border-t border-gray-200 pt-4 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Total per hari</dt>
                    <dd class="font-medium text-gray-900">Rp{{ number_format($perDayTotal, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Lama sewa</dt>
                    <dd class="font-medium text-gray-900"><span id="sum-days">-</span> hari</dd>
                </div>
                <div class="flex justify-between text-base">
                    <dt class="font-semibold text-gray-900">Total biaya</dt>
                    <dd id="sum-total" data-per-day="{{ $perDayTotal }}" class="font-extrabold text-brand-700">-</dd>
                </div>
            </dl>

            <button type="submit" class="btn-primary mt-5 w-full py-3">Kirim Pesanan</button>
            <p class="mt-2 text-center text-xs text-gray-500">Status awal pesanan: Menunggu Konfirmasi.</p>
            <a href="{{ route('cart.index') }}" class="mt-3 block text-center text-sm font-semibold text-brand-700 hover:underline">← Kembali ke keranjang</a>
        </aside>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const pickupInput = document.getElementById('pickup_date');
    const returnInput = document.getElementById('return_date');
    const daysEl = document.getElementById('sum-days');
    const totalEl = document.getElementById('sum-total');
    const perDay = Number(totalEl.dataset.perDay);
    const rupiah = new Intl.NumberFormat('id-ID');

    function updateSummary() {
        if (pickupInput.value) {
            returnInput.min = pickupInput.value;
        }

        if (!pickupInput.value || !returnInput.value) {
            daysEl.textContent = '-';
            totalEl.textContent = '-';
            return;
        }

        const diff = new Date(returnInput.value) - new Date(pickupInput.value);

        if (diff < 0) {
            daysEl.textContent = '-';
            totalEl.textContent = '-';
            return;
        }

        const days = Math.max(1, Math.round(diff / 86400000));
        daysEl.textContent = days;
        totalEl.textContent = 'Rp' + rupiah.format(perDay * days);
    }

    pickupInput.addEventListener('change', updateSummary);
    returnInput.addEventListener('change', updateSummary);
    updateSummary();
</script>
@endpush