<x-layouts.app title="Menu">
    <div class="grid gap-8 md:grid-cols-[1fr_20rem]">
        <section>
            <h1 class="mb-4 text-2xl font-semibold">Menu Kantin</h1>

            <form id="order-form" class="space-y-4 rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="grid gap-3 sm:grid-cols-2">
                    @forelse ($menus as $menu)
                        <label class="flex cursor-pointer items-center justify-between gap-3 rounded-lg border border-stone-200 px-4 py-3 has-checked:border-orange-500 has-checked:bg-orange-50">
                            <span>
                                <span class="block font-medium">{{ $menu->nama }}</span>
                                <span class="text-sm text-stone-500">Rp{{ number_format($menu->harga, 0, ',', '.') }}</span>
                            </span>
                            <input type="radio" name="menu_id" value="{{ $menu->id }}" data-harga="{{ $menu->harga }}" class="accent-orange-600" required>
                        </label>
                    @empty
                        <p class="text-stone-500">Belum ada menu tersedia.</p>
                    @endforelse
                </div>

                <div class="flex flex-wrap items-end gap-4">
                    <label class="text-sm">
                        <span class="mb-1 block font-medium">Jumlah porsi</span>
                        <input type="number" name="jumlah" value="1" min="1" max="50" required
                               class="w-28 rounded-lg border border-stone-300 px-3 py-2 focus:border-orange-500 focus:outline-none">
                    </label>
                    {{-- Display only. Sent to show the server ignores it (slide: "Tampilan harga" in the frontend). --}}
                    <input type="hidden" name="total_harga" value="0">
                    <p class="text-sm text-stone-500">Perkiraan total: <strong id="total" class="text-lg text-stone-800">Rp0</strong></p>
                    <button class="ml-auto rounded-lg bg-orange-600 px-5 py-2 font-medium text-white hover:bg-orange-700 disabled:opacity-50">Pesan</button>
                </div>

                <p id="order-error" class="hidden rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700"></p>
            </form>
        </section>

        <aside class="space-y-8">
            <section>
                <h2 class="mb-3 text-lg font-semibold">Pesanan Saya</h2>
                <ul class="divide-y divide-stone-200 rounded-xl border border-stone-200 bg-white">
                    @forelse ($orders as $order)
                        <li>
                            <a href="{{ route('orders.show', $order) }}" class="block px-4 py-3 hover:bg-stone-50">
                                <span class="flex justify-between text-sm font-medium">
                                    <span>{{ $order->jumlah }}× {{ $order->menu->nama }}</span>
                                    <span>Rp{{ number_format($order->total_harga, 0, ',', '.') }}</span>
                                </span>
                                <span class="mt-1 flex justify-between text-xs text-stone-500">
                                    <x-status :status="$order->status" />
                                    {{ $order->created_at->format('d/m H:i') }}
                                </span>
                            </a>
                        </li>
                    @empty
                        <li class="px-4 py-3 text-sm text-stone-500">Belum ada pesanan.</li>
                    @endforelse
                </ul>
            </section>

            <section>
                <h2 class="mb-3 text-lg font-semibold">Foto Profil</h2>
                <form method="POST" action="{{ route('avatar.store') }}" enctype="multipart/form-data" class="space-y-3 rounded-xl border border-stone-200 bg-white p-4">
                    @csrf
                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" required class="block w-full text-sm">
                    <p class="text-xs text-stone-500">JPG, PNG, atau WEBP, maks. 1 MB.</p>
                    <button class="rounded-lg border border-stone-300 px-4 py-1.5 text-sm hover:bg-stone-50">Unggah</button>
                </form>
            </section>
        </aside>
    </div>

    <script>
        const form = document.getElementById('order-form');
        const totalEl = document.getElementById('total');
        const errorEl = document.getElementById('order-error');
        const rupiah = (n) => 'Rp' + n.toLocaleString('id-ID');

        function recalc() {
            const menu = form.querySelector('input[name=menu_id]:checked');
            const total = menu ? Number(menu.dataset.harga) * Number(form.jumlah.value || 0) : 0;
            form.total_harga.value = total;
            totalEl.textContent = rupiah(total);
        }
        form.addEventListener('input', recalc);

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            errorEl.classList.add('hidden');
            const button = form.querySelector('button');
            button.disabled = true;

            const data = Object.fromEntries(new FormData(form));
            const response = await fetch(@js(route('orders.store')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({
                    menu_id: Number(data.menu_id),
                    jumlah: Number(data.jumlah),
                    total_harga: Number(data.total_harga),
                }),
            });
            const body = await response.json();

            if (response.ok) {
                window.location = body.url;
                return;
            }
            errorEl.textContent = body.errors ? Object.values(body.errors).flat().join(' ') : (body.message || 'Pesanan gagal.');
            errorEl.classList.remove('hidden');
            button.disabled = false;
        });
    </script>
</x-layouts.app>
