<x-layouts.app title="Masuk">
    <div class="mx-auto mt-12 max-w-sm">
        <h1 class="mb-1 text-center text-3xl font-semibold text-orange-600">KampusFood</h1>
        <p class="mb-8 text-center text-sm text-stone-500">Pesan makanan kantin kampus tanpa antre.</p>

        <form method="POST" action="{{ url('/login') }}" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            @csrf
            <label class="block text-sm">
                <span class="mb-1 block font-medium">Username</span>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" maxlength="30"
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-orange-500 focus:outline-none">
            </label>
            <label class="block text-sm">
                <span class="mb-1 block font-medium">Password</span>
                <input type="password" name="password" required autocomplete="current-password"
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-orange-500 focus:outline-none">
            </label>
            <button class="w-full rounded-lg bg-orange-600 py-2 font-medium text-white hover:bg-orange-700">Masuk</button>
        </form>
    </div>
</x-layouts.app>
