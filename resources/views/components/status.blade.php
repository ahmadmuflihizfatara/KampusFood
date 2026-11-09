@props(['status'])

@php
    [$label, $classes] = match ($status) {
        \App\Models\Order::Dibayar => ['Dibayar', 'bg-blue-100 text-blue-800'],
        \App\Models\Order::Selesai => ['Selesai', 'bg-green-100 text-green-800'],
        default => ['Menunggu pembayaran', 'bg-amber-100 text-amber-800'],
    };
@endphp

<span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium {{ $classes }}">{{ $label }}</span>
