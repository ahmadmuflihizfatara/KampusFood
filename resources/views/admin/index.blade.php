<x-layouts.app title="Admin Kantin">
    <h1 class="mb-6 text-2xl font-semibold">Admin Kantin</h1>

    <section class="mb-10">
        <h2 class="mb-3 text-lg font-semibold">Pesanan Masuk</h2>
        <div class="overflow-x-auto rounded-xl border border-stone-200 bg-white">
            <table class="w-full text-sm">
                <thead class="bg-stone-100 text-left text-xs uppercase text-stone-500">
                    <tr><th class="px-4 py-2">#</th><th class="px-4 py-2">Pemesan</th><th class="px-4 py-2">Pesanan</th><th class="px-4 py-2 text-right">Total</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-4 py-2"><a href="{{ route('orders.show', $order) }}" class="text-orange-600 hover:underline">{{ $order->id }}</a></td>
                            <td class="px-4 py-2">{{ $order->user->name }}</td>
                            <td class="px-4 py-2">{{ $order->jumlah }}× {{ $order->menu->nama }}</td>
                            <td class="px-4 py-2 text-right">Rp{{ number_format($order->total_harga, 0, ',', '.') }}</td>
                            <td class="px-4 py-2"><x-status :status="$order->status" /></td>
                            <td class="px-4 py-2 text-right">
                                @if ($order->status === \App\Models\Order::Dibayar)
                                    <form method="POST" action="{{ route('admin.orders.complete', $order) }}">
                                        @csrf
                                        <button class="rounded-lg bg-green-600 px-3 py-1 text-xs font-medium text-white hover:bg-green-700">Selesai</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-3 text-stone-500">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2 class="mb-3 text-lg font-semibold">Menu & Harga Resmi</h2>
        <div class="space-y-2">
            @foreach ($menus as $menu)
                <form method="POST" action="{{ route('admin.menu.update', $menu) }}" class="flex flex-wrap items-center gap-3 rounded-xl border border-stone-200 bg-white px-4 py-3">
                    @csrf
                    <span class="w-10 text-xs text-stone-400">#{{ $menu->id }}</span>
                    <input name="nama" value="{{ $menu->nama }}" required maxlength="100" class="flex-1 rounded-lg border border-stone-300 px-3 py-1.5 text-sm">
                    <label class="flex items-center gap-1 text-sm">Rp<input type="number" name="harga" value="{{ $menu->harga }}" min="500" max="1000000" required class="w-28 rounded-lg border border-stone-300 px-3 py-1.5"></label>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="tersedia" value="1" @checked($menu->tersedia) class="accent-orange-600"> Tersedia</label>
                    <button class="rounded-lg border border-stone-300 px-3 py-1.5 text-sm hover:bg-stone-50">Simpan</button>
                </form>
            @endforeach

            <form method="POST" action="{{ route('admin.menu.store') }}" class="flex flex-wrap items-center gap-3 rounded-xl border border-dashed border-orange-300 bg-orange-50 px-4 py-3">
                @csrf
                <span class="w-10 text-xs text-orange-600">Baru</span>
                <input name="nama" placeholder="Nama menu" required maxlength="100" class="flex-1 rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-sm">
                <label class="flex items-center gap-1 text-sm">Rp<input type="number" name="harga" min="500" max="1000000" required class="w-28 rounded-lg border border-stone-300 bg-white px-3 py-1.5"></label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="tersedia" value="1" checked class="accent-orange-600"> Tersedia</label>
                <button class="rounded-lg bg-orange-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-orange-700">Tambah</button>
            </form>
        </div>
    </section>

    <section class="mt-10">
        <h2 class="mb-3 text-lg font-semibold">Akun Pengguna</h2>
        <div class="grid gap-6 md:grid-cols-[1fr_20rem]">
            <div class="overflow-x-auto rounded-xl border border-stone-200 bg-white">
                <table class="w-full text-sm">
                    <thead class="bg-stone-100 text-left text-xs uppercase text-stone-500">
                        <tr><th class="px-4 py-2">Nama</th><th class="px-4 py-2">Username</th><th class="px-4 py-2">Email</th><th class="px-4 py-2">Role</th></tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-4 py-2">{{ $user->name }}</td>
                                <td class="px-4 py-2 font-mono text-xs">{{ $user->username }}</td>
                                <td class="px-4 py-2 text-stone-500">{{ $user->email }}</td>
                                <td class="px-4 py-2">
                                    <span @class(['rounded-full px-2.5 py-0.5 text-xs font-medium', 'bg-orange-100 text-orange-800' => $user->isAdmin(), 'bg-stone-100 text-stone-700' => ! $user->isAdmin()])>{{ $user->role }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3 rounded-xl border border-dashed border-orange-300 bg-orange-50 p-4 text-sm">
                @csrf
                <p class="font-medium text-orange-700">Buat akun baru</p>
                <input name="name" value="{{ old('name') }}" placeholder="Nama lengkap" required maxlength="100" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-1.5">
                <input name="username" value="{{ old('username') }}" placeholder="Username" required minlength="3" maxlength="30" pattern="[A-Za-z0-9_\-]+" autocomplete="off" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-1.5">
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required class="w-full rounded-lg border border-stone-300 bg-white px-3 py-1.5">
                <input type="password" name="password" placeholder="Password (min. 8, huruf + angka)" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-1.5">
                <input type="password" name="password_confirmation" placeholder="Ulangi password" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-1.5">
                <select name="role" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-1.5">
                    <option value="mahasiswa" @selected(old('role', 'mahasiswa') === 'mahasiswa')>Mahasiswa</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin kantin</option>
                </select>
                <button class="w-full rounded-lg bg-orange-600 py-1.5 font-medium text-white hover:bg-orange-700">Buat akun</button>
            </form>
        </div>
    </section>
</x-layouts.app>
