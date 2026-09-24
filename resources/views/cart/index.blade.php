<div>
    <!-- An unexamined life is not worth living. - Socrates -->
</div>
@extends('layouts.app')

@section('title', 'Keranjang Penyewaan')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10">
    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Keranjang Penyewaan</h1>

    @if ($lines->isEmpty())
        <div class="mt-8 rounded-2xl bg-white p-12 text-center ring-1 ring-gray-200">
            <p class="text-4xl" aria-hidden="true">🎒</p>
            <p class="mt-3 font-semibold text-gray-900">Keranjang masih kosong</p>
            <p class="mt-1 text-sm text-gray-500">Pilih alat camping di katalog untuk mulai menyewa.</p>
            <a href="{{ route('catalog.index') }}" class="btn-primary mt-6">Lihat Katalog</a>
        </div>
    @else
        <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_22rem]">

            <div class="space-y-4">
                @foreach ($lines as $line)
                    @php $product = $line['product']; @endphp

                    <div class="flex gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
                        <a href="{{ route('catalog.show', $product->slug) }}"
                           class="h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-gray-100">
                            @if ($product->thumbnail_url)
                                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            @else
                                <span class="flex h-full items-center justify-center text-3xl" aria-hidden="true">⛺</span>
                            @endif
                        </a>

                        <div class="flex flex-1 flex-col">
                            <a href="{{ route('catalog.show', $product->slug) }}"
                               class="font-semibold text-gray-900 hover:text-brand-700">{{ $product->name }}</a>
                            <p class="text-sm text-gray-500">{{ $product->category->name }} · {{ $product->price_formatted }} / hari</p>

                            <div class="mt-auto flex flex-wrap items-end justify-between gap-3 pt-3">
                                <form method="POST" action="{{ route('cart.update', $product) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $line['quantity'] }}"
                                           min="1" max="{{ max(1, $product->stock) }}"
                                           class="form-input mt-0 w-20" aria-label="Jumlah {{ $product->name }}">
                                    <button type="submit" class="btn-outline">Perbarui</button>
                                </form>

                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Per hari</p>
                                    <p class="font-bold text-brand-700">Rp{{ number_format($line['per_day'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('cart.destroy', $product) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:underline"
                                    aria-label="Hapus {{ $product->name }} dari keranjang">Hapus</button>
                        </form>
                    </div>
                @endforeach

                @error('quantity')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <aside class="h-fit rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 lg:sticky lg:top-24">
                <h2 class="font-semibold text-gray-900">Ringkasan</h2>

                <div class="mt-4 flex justify-between text-sm">
                    <span class="text-gray-600">Total per hari</span>
                    <span class="font-semibold text-gray-900">Rp{{ number_format($perDayTotal, 0, ',', '.') }}</span>
                </div>

                <div class="mt-5 rounded-xl bg-gray-50 p-4">
                    <label for="sim-days" class="form-label">Simulasi lama sewa (hari)</label>
                    <input id="sim-days" type="number" min="1" max="30" value="1" class="form-input">
                    <p class="mt-3 text-xs text-gray-500">Perkiraan total</p>
                    <p id="sim-total" data-per-day="{{ $perDayTotal }}" class="text-xl font-extrabold text-brand-700">
                        Rp{{ number_format($perDayTotal, 0, ',', '.') }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">Total pasti dihitung di halaman checkout dari tanggal ambil dan kembali.</p>
                </div>

                <a href="{{ route('checkout.create') }}" class="btn-primary mt-5 w-full py-3">Lanjut ke Checkout</a>

                @guest
                    <p class="mt-2 text-center text-xs text-gray-500">Anda akan diminta masuk atau mendaftar dulu.</p>
                @endguest

                <a href="{{ route('catalog.index') }}"
                   class="mt-4 block text-center text-sm font-semibold text-brand-700 hover:underline">+ Tambah alat lain</a>
            </aside>
        </div>
    @endif
</div>
@endsection

@if ($lines->isNotEmpty())
@push('scripts')
<script>
    const simInput = document.getElementById('sim-days');
    const simTotal = document.getElementById('sim-total');
    const simPerDay = Number(simTotal.dataset.perDay);
    const simFormat = new Intl.NumberFormat('id-ID');

    function updateSimulation() {
        const days = Math.min(30, Math.max(1, parseInt(simInput.value, 10) || 1));
        simTotal.textContent = 'Rp' + simFormat.format(simPerDay * days);
    }

    simInput.addEventListener('input', updateSimulation);
    updateSimulation();
</script>
@endpush
@endif