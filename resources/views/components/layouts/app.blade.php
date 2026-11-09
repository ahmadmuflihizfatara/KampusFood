<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'KampusFood' }} · KampusFood</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-800 antialiased">
    @auth
        <header class="border-b border-stone-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-3">
                <a href="{{ route('home') }}" class="text-lg font-semibold text-orange-600">KampusFood</a>
                <nav class="flex items-center gap-4 text-sm">
                    @can('admin')
                        <a href="{{ route('admin.index') }}" class="hover:text-orange-600">Admin Kantin</a>
                    @endcan
                    <span class="flex items-center gap-2">
                        @if (auth()->user()->avatar_path)
                            <img src="{{ route('avatar.show', auth()->user()) }}" alt="" class="size-7 rounded-full object-cover">
                        @endif
                        {{ auth()->user()->name }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-stone-500 hover:text-stone-800">Keluar</button>
                    </form>
                </nav>
            </div>
        </header>
    @endauth

    <main class="mx-auto max-w-5xl px-4 py-8">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
