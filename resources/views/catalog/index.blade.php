@extends('layouts.app')

@section('title', 'Katalog Alat Camping')
@section('meta_description', 'Daftar lengkap alat camping yang bisa disewa: tenda, carrier, sleeping bag, kompor, dan lainnya.')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10">
    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Katalog Alat Camping</h1>
    <p class="mt-2 text-gray-600">Pilih alat yang Anda butuhkan untuk petualangan berikutnya.</p>

    <form method="GET" action="{{ route('catalog.index') }}"
          class="mt-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto]">
        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Cari tenda, carrier, kompor..."
               class="form-input mt-0" aria-label="Cari produk">

        <select name="category" class="form-input mt-0" aria-label="Kategori">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>

        <select name="sort" class="form-input mt-0" aria-label="Urutkan">
            <option value="latest" @selected($filters['sort'] === 'latest')>Terbaru</option>
            <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Harga terendah</option>
            <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Harga tertinggi</option>
            <option value="name" @selected($filters['sort'] === 'name')>Nama A-Z</option>
        </select>

        <button type="submit" class="btn-primary">Cari</button>
    </form>

    <div class="mt-6 flex items-center justify-between text-sm text-gray-600">
        <p>Menampilkan <strong>{{ $products->total() }}</strong> produk</p>

        @if ($filters['q'] !== '' || $filters['category'] !== '' || $filters['sort'] !== 'latest')
            <a href="{{ route('catalog.index') }}" class="font-semibold text-brand-700 hover:underline">Reset filter</a>
        @endif
    </div>

    @if ($products->isEmpty())
        <div class="mt-6 rounded-2xl bg-white p-12 text-center ring-1 ring-gray-200">
            <p class="text-4xl" aria-hidden="true">🔍</p>
            <p class="mt-3 font-semibold text-gray-900">Produk tidak ditemukan</p>
            <p class="mt-1 text-sm text-gray-500">Coba kata kunci lain atau ubah filter kategori.</p>
        </div>
    @else
        <div class="mt-6 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
            @foreach ($products as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="mt-10">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection