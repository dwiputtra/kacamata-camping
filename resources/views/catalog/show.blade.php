@extends('layouts.app')

@php
    $gallery = collect([$product->thumbnail_url])
        ->merge($product->images->map->url)
        ->filter()
        ->unique()
        ->values();

    $available = $product->isAvailable();

    $waText = rawurlencode("Halo, saya ingin menyewa {$product->name}. Apakah tersedia?");
@endphp

@section('title', $product->name)
@section('meta_description', Str::limit(strip_tags((string) $product->description) ?: "Sewa {$product->name} dengan harga {$product->price_formatted} per hari.", 155))
@section('og_type', 'product')
@if ($product->thumbnail_url)
    @section('og_image', $product->thumbnail_url)
@endif

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8">

    <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-brand-700">Beranda</a>
        <span class="mx-1">/</span>
        <a href="{{ route('catalog.index') }}" class="hover:text-brand-700">Katalog</a>
        <span class="mx-1">/</span>
        <span class="text-gray-800">{{ $product->name }}</span>
    </nav>

    <div class="mt-6 grid gap-8 lg:grid-cols-2">

        {{-- Galeri --}}
        <div>
            <div class="aspect-[4/3] overflow-hidden rounded-2xl bg-gray-100 ring-1 ring-gray-200">
                @if ($gallery->isNotEmpty())
                    <img id="main-image" src="{{ $gallery->first() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full items-center justify-center text-7xl" aria-hidden="true">⛺</div>
                @endif
            </div>

            @if ($gallery->count() > 1)
                <div class="mt-3 grid grid-cols-5 gap-2">
                    @foreach ($gallery as $index => $url)
                        <button type="button" data-gallery-thumb data-src="{{ $url }}"
                                class="aspect-square overflow-hidden rounded-lg bg-gray-100 {{ $index === 0 ? 'ring-2 ring-brand-600' : '' }}"
                                aria-label="Lihat foto {{ $index + 1 }}">
                            <img src="{{ $url }}" alt="" loading="lazy" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Info produk --}}
        <div>
            <a href="{{ route('catalog.index', ['category' => $product->category->slug]) }}"
               class="inline-block rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 hover:bg-brand-100">
                {{ $product->category->name }}
            </a>

            <h1 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl">{{ $product->name }}</h1>

            <p class="mt-4 text-3xl font-extrabold text-brand-700">
                {{ $product->price_formatted }}
                <span class="text-base font-normal text-gray-500">/ hari</span>
            </p>

            <div class="mt-4">
                @if ($available)
                    <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-sm font-medium text-brand-700">
                        Tersedia · Stok {{ $product->stock }} unit
                    </span>
                @else
                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-500">
                        Stok habis
                    </span>
                @endif
            </div>

            @if ($product->description)
                <div class="mt-6">
                    <h2 class="font-semibold text-gray-900">Deskripsi</h2>
                    <p class="mt-2 text-sm leading-relaxed text-gray-700">{!! nl2br(e($product->description)) !!}</p>
                </div>
            @endif

            @if (! empty($product->specifications))
                <div class="mt-6">
                    <h2 class="font-semibold text-gray-900">Spesifikasi</h2>
                    <dl class="mt-2 divide-y divide-gray-200 rounded-xl bg-white ring-1 ring-gray-200">
                        @foreach ($product->specifications as $label => $value)
                            <div class="flex justify-between gap-4 px-4 py-2.5 text-sm">
                                <dt class="text-gray-500">{{ $label }}</dt>
                                <dd class="text-right font-medium text-gray-900">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif

                       <div class="mt-8">
                @if ($available)
                    <form method="POST" action="{{ route('cart.store') }}" class="flex flex-col gap-3 sm:flex-row">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}"
                               class="form-input mt-0 w-full sm:w-28" aria-label="Jumlah">
                        <button type="submit" class="btn-primary flex-1 py-3">Tambah ke Keranjang</button>
                    </form>

                    @error('quantity')
                        <p class="form-error">{{ $message }}</p>
                    @enderror

                    <a href="https://wa.me/{{ $site['whatsapp_number'] }}?text={{ $waText }}" target="_blank" rel="noopener noreferrer"
                       class="mt-3 block text-center text-sm font-semibold text-brand-700 hover:underline">Tanya dulu lewat WhatsApp</a>
                @else
                    <span class="btn-outline w-full cursor-not-allowed py-3 opacity-60">Tidak tersedia</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Produk terkait --}}
    @if ($related->isNotEmpty())
        <section class="mt-16">
            <h2 class="text-xl font-bold text-gray-900">Produk Serupa</h2>
            <div class="mt-5 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                @foreach ($related as $item)
                    @include('partials.product-card', ['product' => $item])
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-gallery-thumb]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('main-image').src = button.dataset.src;

            document.querySelectorAll('[data-gallery-thumb]').forEach((b) => {
                b.classList.remove('ring-2', 'ring-brand-600');
            });
            button.classList.add('ring-2', 'ring-brand-600');
        });
    });
</script>
@endpush