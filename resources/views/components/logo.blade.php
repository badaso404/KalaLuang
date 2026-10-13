@props([
    'variant' => 'lockup',
    'markClass' => 'size-9',
    'textClass' => 'text-lg',
    'tagline' => false,
])

@if ($variant === 'full')
    <img src="{{ asset('images/logo.png') }}" alt="KalaLuang — Lowongan Fresh Graduate"
         {{ $attributes->class(['h-auto w-auto']) }}>
@elseif ($variant === 'mark')
    <img src="{{ asset('images/logo-mark.png') }}" alt="KalaLuang"
         {{ $attributes->class([$markClass]) }}>
@else
    <span {{ $attributes->class(['flex items-center gap-2.5']) }}>
        <img src="{{ asset('images/logo-mark.png') }}" alt="" class="{{ $markClass }} shrink-0">
        <span class="leading-none">
            <span class="block font-bold tracking-tight {{ $textClass }}">
                <span class="text-brand-700">Kala</span><span class="text-accent-500">Luang</span>
            </span>
            @if ($tagline)
                <span class="mt-1 block text-[0.6rem] font-semibold tracking-widest text-slate-500 uppercase">
                    Lowongan Fresh Graduate
                </span>
            @endif
        </span>
    </span>
@endif
