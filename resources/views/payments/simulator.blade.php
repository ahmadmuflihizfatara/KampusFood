<x-layouts.app title="Simulasi Payment Gateway">
    <div class="mx-auto max-w-md overflow-hidden rounded-xl border-2 border-dashed border-indigo-300 bg-white shadow-sm">
        <div class="bg-indigo-600 px-6 py-3 text-white">
            <p class="text-xs uppercase tracking-wide opacity-80">Layanan eksternal · simulasi</p>
            <p class="text-lg font-semibold">PayGate Demo</p>
        </div>

        <div class="space-y-4 p-6">
            <div class="text-sm">
                <p class="text-stone-500">Tagihan dari KampusFood</p>
                <p class="text-2xl font-semibold">Rp{{ number_format($order->total_harga, 0, ',', '.') }}</p>
                <p class="font-mono text-xs text-stone-500">{{ $order->payment_reference }}</p>
            </div>

            <button data-mode="valid" class="w-full rounded-lg bg-indigo-600 py-2 font-medium text-white hover:bg-indigo-700">Bayar sekarang</button>
            <button data-mode="tampered" class="w-full rounded-lg border border-red-300 py-2 text-sm text-red-700 hover:bg-red-50">Uji: kirim callback dengan nominal dimanipulasi</button>

            <pre id="result" class="hidden whitespace-pre-wrap rounded-lg bg-stone-900 p-3 text-xs text-stone-100"></pre>
        </div>
    </div>

    <script>
        const payload = @js($payload);
        const signature = @js($signature);
        const result = document.getElementById('result');

        document.querySelectorAll('button[data-mode]').forEach((button) => {
            button.addEventListener('click', async () => {
                // "tampered" changes the amount but keeps the original signature: the backend must reject it.
                const body = button.dataset.mode === 'valid'
                    ? payload
                    : JSON.stringify({ ...JSON.parse(payload), amount: 1 });

                const response = await fetch(@js(route('payments.callback')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Signature': signature },
                    body,
                });
                const json = await response.json();
                result.textContent = `HTTP ${response.status}\n${JSON.stringify(json, null, 2)}`;
                result.classList.remove('hidden');

                if (response.ok) {
                    setTimeout(() => window.location = @js(route('orders.show', $order)), 1200);
                }
            });
        });
    </script>
</x-layouts.app>
