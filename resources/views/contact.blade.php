@extends('layouts.app')

@section('title', 'Kontak')
@section('meta_description', 'Hubungi kami lewat WhatsApp, email, atau kunjungi langsung toko penyewaan alat camping kami.')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10">
    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Kontak Kami</h1>
    <p class="mt-2 text-gray-600">Ada pertanyaan soal alat atau pesanan? Hubungi kami kapan saja.</p>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="space-y-4">
            <a href="https://wa.me/{{ $site['whatsapp_number'] }}" target="_blank" rel="noopener noreferrer"
               class="flex items-start gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:ring-brand-300">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-green-100 text-xl" aria-hidden="true">💬</span>
                <span>
                    <span class="block text-sm text-gray-500">WhatsApp</span>
                    <span class="block font-semibold text-gray-900">{{ $site['whatsapp'] }}</span>
                    <span class="text-xs text-brand-700">Klik untuk chat</span>
                </span>
            </a>

            <a href="mailto:{{ $site['email'] }}"
               class="flex items-start gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:ring-brand-300">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xl" aria-hidden="true">✉️</span>
                <span>
                    <span class="block text-sm text-gray-500">Email</span>
                    <span class="block font-semibold text-gray-900">{{ $site['email'] }}</span>
                </span>
            </a>

            <div class="flex items-start gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xl" aria-hidden="true">📍</span>
                <span>
                    <span class="block text-sm text-gray-500">Alamat</span>
                    <span class="block font-semibold text-gray-900">{!! nl2br(e($site['address'])) !!}</span>
                </span>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            @if ($site['maps_embed'] && Str::startsWith($site['maps_embed'], 'https://www.google.com/maps'))
                <iframe src="{{ $site['maps_embed'] }}" title="Lokasi toko di Google Maps"
                        class="h-80 w-full lg:h-full lg:min-h-96" style="border:0" loading="lazy" allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
            @else
                <div class="flex h-80 flex-col items-center justify-center gap-2 p-6 text-center text-sm text-gray-500 lg:h-full">
                    <span class="text-4xl" aria-hidden="true">🗺️</span>
                    Peta belum diatur. Admin dapat mengisinya di pengaturan website.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection