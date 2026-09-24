@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10">
    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Pesanan Saya</h1>
    <p class="mt-2 text-gray-600">Riwayat dan status semua penyewaan Anda.</p>

    @if ($rentals->isEmpty())
        <div class="mt-8 rounded-2xl bg-white p-12 text-center ring-1 ring-gray-200">
            <p class="text-4xl" aria-hidden="true">📦</p>
            <p class="mt-3 font-semibold text-gray-900">Belum ada pesanan</p>
            <p class="mt-1 text-sm text-gray-500">Pesanan yang Anda buat akan muncul di sini.</p>
            <a href="{{ route('catalog.index') }}" class="btn-primary mt-6">Lihat Katalog</a>
        </div>
    @else
        <div class="mt-8 space-y-4">
            @foreach ($rentals as $rental)
                <a href="{{ route('orders.show', $rental->rental_code) }}"
                   class="block rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:ring-brand-300">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="font-bold tracking-wide text-gray-900">{{ $rental->rental_code }}</p>
                            <p class="text-xs text-gray-500">Dibuat {{ $rental->created_at->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                        @include('partials.rental-status', ['rental' => $rental])
                    </div>

                    <div class="mt-4 flex flex-wrap items-end justify-between gap-2 text-sm">
                        <p class="text-gray-600">
                            {{ $rental->pickup_date->translatedFormat('d M Y') }} – {{ $rental->return_date->translatedFormat('d M Y') }}
                            · {{ $rental->total_days }} hari · {{ $rental->items_count }} jenis alat
                        </p>
                        <p class="text-lg font-extrabold text-brand-700">{{ $rental->total_price_formatted }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $rentals->links() }}
        </div>
    @endif
</div>
@endsection