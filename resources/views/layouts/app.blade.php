<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sewa Alat Camping') | {{ $site['site_name'] }}</title>
    <meta name="description" content="@yield('meta_description', $site['tagline'])">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:site_name" content="{{ $site['site_name'] }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', 'Sewa Alat Camping') | {{ $site['site_name'] }}">
    <meta property="og:description" content="@yield('meta_description', $site['tagline'])">
    <meta property="og:url" content="{{ url()->current() }}">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-gray-50 font-sans text-gray-800 antialiased">
@php
    $navLink = fn (bool $active) => $active ? 'font-semibold text-brand-700' : 'font-medium text-gray-700 hover:text-brand-700';
@endphp

    <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-extrabold uppercase tracking-wide text-brand-700">
                @if ($site['logo_url'])
                    <img src="{{ $site['logo_url'] }}" alt="{{ $site['site_name'] }}" class="h-9 w-auto">
                @else
                    {{ $site['site_name'] }}
                @endif
            </a>

            <button id="nav-toggle" type="button"
                    class="rounded-md p-2 text-gray-600 hover:bg-gray-100 md:hidden"
                    aria-label="Buka menu" aria-controls="nav-menu" aria-expanded="false">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <nav id="nav-menu"
                 class="absolute inset-x-0 top-16 hidden flex-col gap-3 border-b border-gray-200 bg-white p-4 md:static md:flex md:flex-row md:items-center md:gap-6 md:border-0 md:bg-transparent md:p-0">
                <a href="{{ route('home') }}" class="text-sm {{ $navLink(request()->routeIs('home')) }}">Beranda</a>
                <a href="{{ route('catalog.index') }}" class="text-sm {{ $navLink(request()->routeIs('catalog.*')) }}">Katalog</a>
                <a href="{{ route('contact') }}" class="text-sm {{ $navLink(request()->routeIs('contact')) }}">Kontak</a>
                                <a href="{{ route('cart.index') }}" class="text-sm {{ $navLink(request()->routeIs('cart.*')) }}">
                    Keranjang
                    @if ($cartCount > 0)
                        <span class="ml-1 rounded-full bg-brand-600 px-2 py-0.5 text-xs font-semibold text-white">{{ $cartCount }}</span>
                    @endif
                </a>

                @auth
                                    <a href="{{ route('orders.index') }}" class="text-sm {{ $navLink(request()->routeIs('orders.*')) }}">Pesanan Saya</a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ url('/admin') }}" class="text-sm font-medium text-gray-700 hover:text-brand-700">Panel Admin</a>
                    @endif

                    <span class="max-w-40 truncate text-sm text-gray-500">Halo, {{ auth()->user()->name }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-outline">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-brand-700">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>

    @if (session('success'))
        <div class="mx-auto mt-4 w-full max-w-6xl px-4">
            <div class="rounded-lg border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mx-auto mt-4 w-full max-w-6xl px-4">
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="mt-16 bg-brand-900 text-brand-100">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 md:grid-cols-3">
            <div>
                <p class="text-lg font-extrabold uppercase tracking-wide text-white">{{ $site['site_name'] }}</p>
                <p class="mt-3 text-sm text-brand-200">{{ $site['tagline'] }}</p>
            </div>

            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-white">Menu</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                    <li><a href="{{ route('catalog.index') }}" class="hover:text-white">Katalog Alat</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white">Kontak</a></li>
                </ul>
            </div>

            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-white">Hubungi Kami</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li>{{ $site['address'] }}</li>
                    <li><a href="mailto:{{ $site['email'] }}" class="hover:text-white">{{ $site['email'] }}</a></li>
                    <li><a href="https://wa.me/{{ $site['whatsapp_number'] }}" target="_blank" rel="noopener noreferrer" class="hover:text-white">WhatsApp: {{ $site['whatsapp'] }}</a></li>
                </ul>

                <div class="mt-4 flex flex-wrap gap-3 text-sm">
                    @foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok'] as $key => $label)
                        @if ($site[$key] && Str::startsWith($site[$key], ['http://', 'https://']))
                            <a href="{{ $site[$key] }}" target="_blank" rel="noopener noreferrer"
                               class="rounded-full bg-white/10 px-3 py-1 hover:bg-white/20">{{ $label }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="border-t border-white/10 py-4 text-center text-xs text-brand-300">
            &copy; {{ date('Y') }} {{ $site['site_name'] }}. Semua hak dilindungi.
        </div>
    </footer>

    <a href="https://wa.me/{{ $site['whatsapp_number'] }}" target="_blank" rel="noopener noreferrer"
       class="fixed bottom-5 right-5 z-40 inline-flex items-center gap-2 rounded-full bg-green-500 px-4 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-green-600"
       aria-label="Chat WhatsApp">
        <span aria-hidden="true">💬</span>
        <span class="hidden sm:inline">Chat WhatsApp</span>
    </a>

    @stack('scripts')
</body>
</html>