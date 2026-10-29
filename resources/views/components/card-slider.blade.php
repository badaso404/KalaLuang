@props([
    'heading',
    'subheading' => null,
    'href' => null,
    'linkLabel' => 'Lihat semua',
])

{{--
    Baris kartu yang bisa digeser horizontal. Kartu di dalam slot wajib memakai
    kelas lebar tetap + `snap-start` (lihat pemakaiannya di beranda publik).
--}}
<section data-carousel>
    <div class="flex items-end justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 sm:text-xl">{{ $heading }}</h2>
            @if ($subheading)
                <p class="mt-1 text-sm text-slate-600">{{ $subheading }}</p>
            @endif
        </div>

        <div class="flex shrink-0 items-center gap-2">
            @if ($href)
                <a href="{{ $href }}" class="text-sm font-semibold text-brand-700 hover:underline">{{ $linkLabel }}</a>
            @endif
            <button type="button" data-carousel-prev class="btn-secondary hidden px-3 sm:inline-flex"
                    aria-label="Geser ke kiri">
                <x-arrow direction="left" />
            </button>
            <button type="button" data-carousel-next class="btn-secondary hidden px-3 sm:inline-flex"
                    aria-label="Geser ke kanan">
                <x-arrow direction="right" />
            </button>
        </div>
    </div>

    <div data-carousel-track
         class="mt-4 flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        {{ $slot }}
    </div>
</section>
