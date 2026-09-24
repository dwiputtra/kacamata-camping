@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
<div class="mx-auto flex max-w-2xl flex-col items-center px-4 py-24 text-center">
    <p class="text-7xl" aria-hidden="true">🧭</p>
    <h1 class="mt-6 text-3xl font-bold text-gray-900">404 - Halaman Tidak Ditemukan</h1>
    <p class="mt-3 text-gray-600">Sepertinya Anda tersesat di jalur pendakian. Halaman yang Anda cari tidak ada atau sudah dipindahkan.</p>

    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
        <a href="{{ route('home') }}" class="btn-primary">Kembali ke Beranda</a>
        <a href="{{ route('catalog.index') }}" class="btn-outline">Lihat Katalog</a>
    </div>
</div>
@endsection