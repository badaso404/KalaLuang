@props(['promos'])

{{--
    Carousel kartu promosi beranda publik. Isinya dibaca dari
    config('kalaluang.promos'); mekanisme geser & titik navigasi ditangani
    resources/js/app.js lewat atribut data-carousel.
--}}
@if (filled($promos))
    <section data-carousel data-carousel-autoplay="7000" class="relative"
             aria-roledescription="carousel" aria-label="Sorotan KalaLuang">
        <div data-carousel-track
             class="flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            @foreach ($promos as $index => $promo)
                @php
                    $gradient = match ($promo['theme'] ?? 'brand') {
                        'accent' => 'from-accent-600 via-accent-500 to-accent-500',
                        'navy' => 'from-brand-900 via-brand-700 to-brand-600',
                        'teal' => 'from-teal-700 via-teal-600 to-emerald-500',
                        'slate' => 'from-slate-900 via-slate-800 to-slate-700',
                        default => 'from-brand-700 via-brand-600 to-brand-500',
                    };
                @endphp

                <article
                    class="relative w-full shrink-0 snap-center overflow-hidden rounded-2xl bg-gradient-to-br {{ $gradient }} p-6 text-white sm:p-10"
                    role="group" aria-roledescription="slide"
                    aria-label="Slide {{ $index + 1 }} dari {{ count($promos) }}">
                    <div aria-hidden="true"
                         class="pointer-events-none absolute -top-16 -right-16 size-56 rounded-full border-[18px] border-white/10 sm:size-72"></div>
                    <div aria-hidden="true"
                         class="pointer-events-none absolute -bottom-20 right-20 size-40 rounded-full bg-white/5"></div>

                    <div class="relative max-w-xl">
                        <p class="text-xs font-semibold tracking-widest uppercase text-white/80">{{ $promo['eyebrow'] }}</p>
                        <h3 class="mt-2 text-xl font-bold sm:text-3xl">{{ $promo['title'] }}</h3>
                        <p class="mt-3 text-sm text-white/90 sm:text-base">{{ $promo['body'] }}</p>
                        <a href="{{ route($promo['cta']['route'], $promo['cta']['params'] ?? []) }}"
                           class="btn mt-5 bg-white text-slate-900 hover:bg-slate-100">
                            {{ $promo['cta']['label'] }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-4 flex items-center justify-between gap-4">
            <div data-carousel-dots class="flex items-center gap-2">
                @foreach ($promos as $index => $promo)
                    <button type="button" data-carousel-dot="{{ $index }}"
                            class="h-2 w-6 rounded-full bg-slate-300 transition aria-[current=true]:w-10 aria-[current=true]:bg-brand-600"
                            aria-current="{{ $index === 0 ? 'true' : 'false' }}">
                        <span class="sr-only">Slide {{ $index + 1 }}: {{ $promo['title'] }}</span>
                    </button>
                @endforeach
            </div>

            <div class="flex gap-2">
                <button type="button" data-carousel-prev class="btn-secondary px-3" aria-label="Slide sebelumnya">
                    <x-arrow direction="left" />
                </button>
                <button type="button" data-carousel-next class="btn-secondary px-3" aria-label="Slide berikutnya">
                    <x-arrow direction="right" />
                </button>
            </div>
        </div>
    </section>
@endif
