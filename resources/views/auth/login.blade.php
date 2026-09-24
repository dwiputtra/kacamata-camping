@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
<div class="mx-auto max-w-md px-4 py-12">
    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-200">
        <h1 class="text-2xl font-bold text-gray-900">Masuk</h1>
        <p class="mt-1 text-sm text-gray-500">Masuk untuk menyewa alat dan melihat status pesanan Anda.</p>

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                       required autofocus autocomplete="email" class="form-input">
                @error('email') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="form-label">Password</label>
                <input id="password" name="password" type="password"
                       required autocomplete="current-password" class="form-input">
                @error('password') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand-600">
                Ingat saya
            </label>

            <button type="submit" class="btn-primary w-full">Masuk</button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-brand-700 hover:underline">Daftar</a>
        </p>
    </div>
</div>
@endsection