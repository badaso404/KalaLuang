@props(['title' => null, 'wide' => false])

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' — '.config('app.name') : config('app.name') }}</title>
    {{-- Bukan /favicon.ico: nginx bawaan Herd memotong path itu sebelum sampai ke Laravel. --}}
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('images/logo-icon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#15417a">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-slate-50 font-sans text-slate-900 antialiased">
    <x-site-header />

    <main class="flex-1">
        <div @class([
            'mx-auto w-full px-4 py-6 sm:px-6 lg:py-10',
            'max-w-7xl' => $wide,
            'max-w-5xl' => ! $wide,
        ])>
            <x-alert />
            {{ $slot }}
        </div>
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-6 text-sm text-slate-500 sm:px-6">
            <x-logo mark-class="size-8" text-class="text-base" tagline />
            <p class="mt-3">Lowongan kerja khusus fresh graduate. Gratis untuk pencari kerja.</p>
        </div>
    </footer>
</body>
</html>
