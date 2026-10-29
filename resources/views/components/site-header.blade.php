@php
    $user = auth()->user();

    // Notifikasi database dari perubahan status lamaran (ApplicationStatusChanged).
    $unreadUpdates = $user?->isJobSeeker() ? $user->unreadNotifications()->count() : 0;

    $links = collect([
        ['label' => 'Beranda', 'route' => 'home', 'show' => true],
        ['label' => 'Lowongan', 'route' => 'vacancies.index', 'show' => true],
        [
            'label' => 'Untuk Perusahaan',
            'route' => 'register',
            'params' => ['peran' => 'company'],
            'show' => $user === null,
        ],
        ['label' => 'Profil Saya', 'route' => 'job-seeker.profile.edit', 'show' => $user?->isJobSeeker()],
        [
            'label' => 'Lamaran',
            'route' => 'job-seeker.applications.index',
            'count' => $unreadUpdates,
            'show' => $user?->isJobSeeker(),
        ],
        ['label' => 'Tersimpan', 'route' => 'job-seeker.saved.index', 'show' => $user?->isJobSeeker()],
        ['label' => 'Dasbor', 'route' => 'company.dashboard', 'show' => $user?->isCompany()],
        ['label' => 'Panel Admin', 'route' => 'admin.dashboard', 'show' => $user?->isAdmin()],
    ])->where('show')->values();
@endphp

<header class="sticky top-0 z-20 border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl items-center gap-4 px-4 py-3 sm:px-6">
        <a href="{{ route('home') }}" class="shrink-0" aria-label="KalaLuang — beranda">
            <x-logo mark-class="size-9" text-class="text-lg" />
        </a>

        <nav class="ml-auto hidden items-center gap-1 md:flex">
            @foreach ($links as $link)
                <a href="{{ route($link['route'], $link['params'] ?? []) }}"
                   @class([
                       'flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition',
                       'bg-brand-50 text-brand-700' => request()->routeIs($link['route']),
                       'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! request()->routeIs($link['route']),
                   ])>
                    {{ $link['label'] }}
                    @if (($link['count'] ?? 0) > 0)
                        <span class="badge bg-accent-500 text-white">
                            {{ $link['count'] }}
                            <span class="sr-only">pembaruan belum dibaca</span>
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-2 md:ml-0">
            @guest
                <a href="{{ route('login') }}" class="btn-secondary hidden sm:inline-flex">Masuk</a>
                <a href="{{ route('register') }}" class="btn-primary">Daftar</a>
            @endguest

            @auth
                <span class="hidden text-right text-xs leading-tight sm:block">
                    <span class="block font-semibold text-slate-800">{{ $user->name }}</span>
                    <span class="text-slate-500">{{ $user->role->label() }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary">Keluar</button>
                </form>
            @endauth
        </div>

        <details class="relative md:hidden">
            <summary class="btn-secondary cursor-pointer list-none">Menu</summary>
            <nav class="absolute right-0 mt-2 w-52 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                @foreach ($links as $link)
                    <a href="{{ route($link['route'], $link['params'] ?? []) }}"
                       class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3 text-sm font-medium text-slate-700 last:border-0 hover:bg-slate-50">
                        {{ $link['label'] }}
                        @if (($link['count'] ?? 0) > 0)
                            <span class="badge bg-accent-500 text-white">{{ $link['count'] }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </details>
    </div>
</header>
