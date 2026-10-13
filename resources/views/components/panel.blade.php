@props(['title', 'links' => [], 'heading' => null])

<x-layout :title="$title" wide>
    <div class="grid gap-6 lg:grid-cols-[13rem_1fr]">
        <nav class="card overflow-hidden p-1 lg:self-start">
            <ul class="flex gap-1 overflow-x-auto lg:flex-col lg:overflow-visible">
                @foreach ($links as $link)
                    <li class="shrink-0 lg:shrink">
                        <a href="{{ $link['url'] }}"
                           @class([
                               'flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition',
                               'bg-brand-50 text-brand-700' => $link['active'] ?? false,
                               'text-slate-600 hover:bg-slate-100' => ! ($link['active'] ?? false),
                           ])>
                            {{ $link['label'] }}
                            @if (($link['count'] ?? 0) > 0)
                                <span class="badge bg-brand-600 text-white">{{ $link['count'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div>
            @if ($heading)
                <h1 class="mb-5 text-xl font-bold text-slate-900 sm:text-2xl">{{ $heading }}</h1>
            @endif
            {{ $slot }}
        </div>
    </div>
</x-layout>
