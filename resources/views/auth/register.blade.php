@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
<div class="mx-auto max-w-md px-4 py-12">
    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-200">
        <h1 class="text-2xl font-bold text-gray-900">Buat Akun</h1>
        <p class="mt-1 text-sm text-gray-500">Daftar gratis untuk mulai menyewa alat camping.</p>

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="name" class="form-label">Nama lengkap</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}"
                       required autofocus autocomplete="name" class="form-input">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                       required autocomplete="email" class="form-input">
                @error('email') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="form-label">Nomor WhatsApp</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}"
                       required autocomplete="tel" placeholder="081234567890" class="form-input">
                @error('phone') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="form-label">Password</label>
                <input id="password" name="password" type="password"
                       required autocomplete="new-password" class="form-input">
                @error('password') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="form-label">Ulangi password</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                       required autocomplete="new-password" class="form-input">
            </div>

            <button type="submit" class="btn-primary w-full">Daftar</button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:underline">Masuk</a>
        </p>
    </div>
</div>
@endsection