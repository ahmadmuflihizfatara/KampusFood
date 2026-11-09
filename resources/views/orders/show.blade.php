<x-layouts.app title="Pesanan #{{ $order->id }}">
    <a href="{{ route('home') }}" class="text-sm text-stone-500 hover:text-stone-800">← Kembali ke menu</a>

    <div class="mx-auto mt-4 max-w-md rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Tagihan #{{ $order->id }}</h1>
            <x-status :status="$order->status" />
        </div>

        <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-stone-500">Pemesan</dt><dd>{{ $order->user->name }}</dd></div>
            <div class="flex justify-between"><dt class="text-stone-500">Menu</dt><dd>{{ $order->menu->nama }}</dd></div>
            <div class="flex justify-between"><dt class="text-stone-500">Jumlah</dt><dd>{{ $order->jumlah }} porsi</dd></div>
            <div class="flex justify-between"><dt class="text-stone-500">Harga satuan</dt><dd>Rp{{ number_format($order->harga_satuan, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between border-t border-stone-200 pt-2 text-base font-semibold"><dt>Total</dt><dd>Rp{{ number_format($order->total_harga, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between text-xs"><dt class="text-stone-500">Ref. pembayaran</dt><dd class="font-mono">{{ $order->payment_reference }}</dd></div>
            @if ($order->paid_at)
                <div class="flex justify-between text-xs"><dt class="text-stone-500">Dibayar</dt><dd>{{ $order->paid_at->format('d/m/Y H:i') }}</dd></div>
            @endif
        </dl>

        @if ($order->status === \App\Models\Order::MenungguPembayaran && config('services.payment.simulator'))
            <a href="{{ route('payments.simulator', $order->payment_reference) }}"
               class="mt-6 block rounded-lg bg-orange-600 py-2 text-center font-medium text-white hover:bg-orange-700">Bayar via Payment Gateway</a>
        @elseif ($order->status === \App\Models\Order::Dibayar)
            <p class="mt-6 rounded-lg bg-blue-50 px-3 py-2 text-center text-sm text-blue-800">Pembayaran diterima. Pesanan sedang disiapkan kantin.</p>
        @endif
    </div>
</x-layouts.app>
