@props(['title' => null, 'wide' => false, 'bleed' => false])

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
        {{-- Halaman "bleed" (beranda publik) mengatur lebar tiap bagiannya sendiri. --}}
        @if ($bleed)
            @if (session('status') || session('error') || $errors->any())
                <div class="mx-auto w-full max-w-7xl px-4 pt-6 sm:px-6">
                    <x-alert />
                </div>
            @endif

            {{ $slot }}
        @else
            <div @class([
                'mx-auto w-full px-4 py-6 sm:px-6 lg:py-10',
                'max-w-7xl' => $wide,
                'max-w-5xl' => ! $wide,
            ])>
                <x-alert />
                {{ $slot }}
            </div>
        @endif
    </main>

    <footer class="mt-auto border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <x-logo mark-class="size-8" text-class="text-base" tagline />
                    <p class="mt-3 max-w-xs text-sm text-slate-600">
                        Lowongan entry-level yang dikurasi manual. Gratis untuk pencari kerja,
                        satu tarif untuk perusahaan.
                    </p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-900">Pencari Kerja</p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li><a href="{{ route('vacancies.index') }}" class="hover:text-brand-700">Katalog lowongan</a></li>
                        <li>
                            <a href="{{ route('vacancies.index', ['employment_type' => 'internship']) }}"
                               class="hover:text-brand-700">Lowongan magang</a>
                        </li>
                        <li>
                            <a href="{{ route('vacancies.index', ['location' => 'remote']) }}"
                               class="hover:text-brand-700">Lowongan remote</a>
                        </li>
                        @guest
                            <li><a href="{{ route('register') }}" class="hover:text-brand-700">Daftar gratis</a></li>
                        @endguest
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-900">Perusahaan</p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        @guest
                            <li>
                                <a href="{{ route('register', ['peran' => 'company']) }}" class="hover:text-brand-700">
                                    Daftar perusahaan
                                </a>
                            </li>
                            <li><a href="{{ route('login') }}" class="hover:text-brand-700">Masuk dasbor</a></li>
                        @endguest
                        @auth
                            @if (auth()->user()->isCompany())
                                <li><a href="{{ route('company.dashboard') }}" class="hover:text-brand-700">Dasbor saya</a></li>
                            @endif
                        @endauth
                        <li class="text-slate-500">
                            Rp{{ number_format(config('kalaluang.listing_price'), 0, ',', '.') }} /
                            lowongan / {{ config('kalaluang.listing_duration_days') }} hari
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-900">Komitmen Kami</p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li>Legalitas perusahaan diperiksa admin</li>
                        <li>Setiap lowongan dikurasi sebelum tayang</li>
                        <li>Tanpa syarat pengalaman kerja</li>
                        <li>Pelamar tidak dipungut biaya apa pun</li>
                    </ul>
                </div>
            </div>

            <p class="mt-8 border-t border-slate-200 pt-6 text-xs text-slate-500">
                &copy; {{ now()->year }} {{ config('app.name') }}. Lowongan kerja khusus fresh graduate.
            </p>
        </div>
    </footer>
</body>
</html>
