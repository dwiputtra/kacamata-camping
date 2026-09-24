@php
    $available = $product->isAvailable();
@endphp

<a href="{{ route('catalog.show', $product->slug) }}"
   class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 transition hover:-translate-y-0.5 hover:shadow-md">
    <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
        @if ($product->thumbnail_url)
            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" loading="lazy"
                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center text-5xl" aria-hidden="true">⛺</div>
        @endif

        <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-xs font-medium text-brand-700 shadow-sm">
            {{ $product->category->name }}
        </span>
    </div>

    <div class="flex flex-1 flex-col p-4">
        <h3 class="font-semibold text-gray-900 group-hover:text-brand-700">{{ $product->name }}</h3>

        <p class="mt-2 text-lg font-bold text-brand-700">
            {{ $product->price_formatted }}
            <span class="text-xs font-normal text-gray-500">/ hari</span>
        </p>

        <div class="mt-auto pt-3">
            @if ($available)
                <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700">
                    Tersedia · {{ $product->stock }} unit
                </span>
            @else
                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500">
                    Tidak tersedia
                </span>
            @endif
        </div>
    </div>
</a>