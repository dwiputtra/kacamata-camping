@extends('layouts.app')

@section('title', 'Sewa Alat Camping Lengkap')
@section('meta_description', $site['tagline'])

@section('content')
@php
    $ctaUrl = $banner?->button_url;
    $ctaUrl = $ctaUrl && Str::startsWith($ctaUrl, ['/', 'http://', 'https://']) ? $ctaUrl : route('catalog.index');
    $ctaText = $banner?->button_text ?: 'Sewa Sekarang';
@endphp

{{-- ================= HERO ================= --}}
<section class="relative isolate overflow-hidden bg-linear-to-br from-brand-900 via-brand-800 to-brand-600 text-white">
    @if ($banner)
        <img src="{{ $banner->image_url }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover">
        <div class="absolute inset-0 -z-10 bg-linear-to-b from-black/60 via-black/40 to-brand-900/80"></div>
    @else
        <svg class="absolute inset-x-0 bottom-0 -z-10 w-full" viewBox="0 0 1440 320" preserveAspectRatio="none" aria-hidden="true">
            <path class="text-brand-900" fill="currentColor" fill-opacity="0.55"
                  d="M0,320 L0,210 L160,110 L290,190 L470,40 L680,220 L860,90 L1040,200 L1220,70 L1440,220 L1440,320 Z" />
            <path class="text-gray-50" fill="currentColor"
                  d="M0,320 L0,270 L200,200 L360,260 L560,170 L760,270 L960,190 L1180,265 L1300,220 L1440,270 L1440,320 Z" />
        </svg>
    @endif

    <div class="mx-auto max-w-6xl px-4 pb-32 pt-24 text-center sm:pb-44 sm:pt-32">
        @if ($banner?->promo_text)
            <span class="mb-5 inline-block rounded-full bg-white/15 px-4 py-1 text-sm font-medium backdrop-blur">
                {{ $banner->promo_text }}
            </span>
        @endif

        <h1 class="text-4xl font-extrabold uppercase tracking-tight sm:text-6xl lg:text-7xl">{{ $site['site_name'] }}</h1>

        <p class="mx-auto mt-5 max-w-2xl text-lg text-white/90 sm:text-xl">{{ $banner?->title ?: $site['tagline'] }}</p>

        @if ($banner?->subtitle)
            <p class="mx-auto mt-2 max-w-2xl text-white/75">{{ $banner->subtitle }}</p>
        @endif

        <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <a href="{{ route('catalog.index') }}"
               class="rounded-lg bg-white px-6 py-3 text-sm font-semibold text-brand-800 shadow hover:bg-brand-50">Lihat Katalog</a>
            <a href="{{ $ctaUrl }}"
               class="rounded-lg bg-brand-500 px-6 py-3 text-sm font-semibold text-white shadow ring-1 ring-white/30 hover:bg-brand-400">{{ $ctaText }}</a>
        </div>
    </div>
</section>

{{-- ================= KENAPA MEMILIH KAMI ================= --}}
<section class="mx-auto max-w-6xl px-4 py-16">
    <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Kenapa Memilih Kami</h2>
        <p class="mt-2 text-gray-600">Berangkat camping tanpa ribet beli alat sendiri.</p>
    </div>

    <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['🧼', 'Alat Bersih & Terawat', 'Setiap alat dicek dan dibersihkan sebelum disewakan.'],
            ['💸', 'Harga Terjangkau', 'Harga sewa per hari yang jelas dan mudah dihitung.'],
            ['⚡', 'Pesan Mudah', 'Pilih alat, tentukan tanggal, lalu pesanan langsung kami proses.'],
            ['💬', 'Respon Cepat', 'Tim kami siap membantu lewat WhatsApp.'],
        ] as [$icon, $title, $text])
            <div class="rounded-2xl bg-white p-6 text-center shadow-sm ring-1 ring-gray-200">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-50 text-2xl" aria-hidden="true">{{ $icon }}</div>
                <h3 class="mt-4 font-semibold text-gray-900">{{ $title }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ $text }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ================= KATEGORI ================= --}}
<section class="bg-white py-16">
    <div class="mx-auto max-w-6xl px-4">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Kategori Alat</h2>
            <p class="mt-2 text-gray-600">Temukan perlengkapan sesuai kebutuhan petualangan Anda.</p>
        </div>

        @if ($categories->isEmpty())
            <p class="mt-10 text-center text-sm text-gray-500">Kategori belum tersedia.</p>
        @else
            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($categories as $category)
                    <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                       class="flex flex-col items-center rounded-2xl bg-gray-50 p-5 text-center ring-1 ring-gray-200 transition hover:bg-brand-50 hover:ring-brand-300">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-100 text-lg font-bold text-brand-700">
                            {{ Str::upper(Str::substr($category->name, 0, 1)) }}
                        </span>
                        <span class="mt-3 text-sm font-semibold text-gray-900">{{ $category->name }}</span>
                        <span class="mt-1 text-xs text-gray-500">{{ $category->products_count }} produk</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ================= PRODUK TERBARU ================= --}}
<section class="mx-auto max-w-6xl px-4 py-16">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Produk Terbaru</h2>
            <p class="mt-2 text-gray-600">Alat camping yang baru masuk katalog.</p>
        </div>
        <a href="{{ route('catalog.index') }}" class="shrink-0 text-sm font-semibold text-brand-700 hover:underline">Lihat semua →</a>
    </div>

    @if ($latestProducts->isEmpty())
        <p class="mt-10 rounded-2xl bg-white p-10 text-center text-sm text-gray-500 ring-1 ring-gray-200">
            Belum ada produk. Tambahkan produk lewat panel admin.
        </p>
    @else
        <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
            @foreach ($latestProducts as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>
    @endif
</section>

{{-- ================= TESTIMONI ================= --}}
@if ($testimonials->isNotEmpty())
<section class="bg-white py-16">
    <div class="mx-auto max-w-6xl px-4">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Kata Mereka</h2>
            <p class="mt-2 text-gray-600">Pengalaman para pelanggan yang sudah menyewa di sini.</p>
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($testimonials as $testimonial)
                <figure class="flex flex-col rounded-2xl bg-gray-50 p-6 ring-1 ring-gray-200">
                    <div class="text-lg text-amber-500" aria-label="Rating {{ $testimonial->rating }} dari 5">
                        {{ str_repeat('★', $testimonial->rating) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $testimonial->rating) }}</span>
                    </div>

                    <blockquote class="mt-3 flex-1 text-sm leading-relaxed text-gray-700">
                        “{{ $testimonial->content }}”
                    </blockquote>

                    <figcaption class="mt-5 flex items-center gap-3">
                        @if ($testimonial->photo_url)
                            <img src="{{ $testimonial->photo_url }}" alt="{{ $testimonial->name }}" loading="lazy"
                                 class="h-10 w-10 rounded-full object-cover">
                        @else
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 font-bold text-brand-700">
                                {{ Str::upper(Str::substr($testimonial->name, 0, 1)) }}
                            </span>
                        @endif
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $testimonial->name }}</p>
                            @if ($testimonial->title)
                                <p class="text-xs text-gray-500">{{ $testimonial->title }}</p>
                            @endif
                        </div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ================= FAQ ================= --}}
<section class="mx-auto max-w-3xl px-4 py-16">
    <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Pertanyaan Umum</h2>
    </div>

    <div class="mt-8 space-y-3">
        @foreach ([
            ['Bagaimana cara menyewa alat?', 'Pilih alat di katalog, masukkan ke keranjang, isi data dan tanggal ambil serta kembali, lalu kirim pesanan. Kami akan mengonfirmasi lewat WhatsApp.'],
            ['Berapa lama minimal sewa?', 'Minimal sewa adalah 1 hari. Biaya dihitung dari jumlah hari dikali harga sewa per hari.'],
            ['Bagaimana cara pembayarannya?', 'Pembayaran dapat dilakukan lewat transfer bank, QRIS, atau tunai. Detailnya kami sampaikan saat pesanan dikonfirmasi.'],
            ['Bagaimana jika alat rusak atau hilang?', 'Mohon jaga alat dengan baik. Jika terjadi kerusakan atau kehilangan, kami akan menghubungi Anda untuk menyelesaikannya.'],
            ['Apakah pesanan bisa dibatalkan?', 'Bisa. Hubungi kami lewat WhatsApp secepatnya agar pesanan dapat kami batalkan.'],
        ] as [$question, $answer])
            <details class="group rounded-xl bg-white ring-1 ring-gray-200">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-sm font-semibold text-gray-900">
                    {{ $question }}
                    <span class="text-brand-600 transition group-open:rotate-180" aria-hidden="true">▾</span>
                </summary>
                <p class="px-5 pb-4 text-sm text-gray-600">{{ $answer }}</p>
            </details>
        @endforeach
    </div>
</section>
@endsection