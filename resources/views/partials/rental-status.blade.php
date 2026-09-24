@php
    $classes = [
        'pending' => 'bg-amber-100 text-amber-800',
        'approved' => 'bg-sky-100 text-sky-800',
        'ongoing' => 'bg-violet-100 text-violet-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ][$rental->status] ?? 'bg-gray-100 text-gray-700';
@endphp

<span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $classes }}">{{ $rental->status_label }}</span>