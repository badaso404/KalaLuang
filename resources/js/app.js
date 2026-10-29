/**
 * Carousel geser-horizontal untuk komponen <x-promo-slider> dan <x-card-slider>.
 *
 * Markup-nya murni scroll-snap, jadi tanpa JS pun kartunya masih bisa digeser
 * dengan sentuhan atau trackpad. JS hanya menambahkan tombol panah, titik
 * navigasi, dan putar otomatis (jika pengguna tidak meminta kurangi animasi).
 */

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function initCarousel(root) {
    const track = root.querySelector('[data-carousel-track]');

    if (! track) {
        return;
    }

    const previousButton = root.querySelector('[data-carousel-prev]');
    const nextButton = root.querySelector('[data-carousel-next]');
    const dots = Array.from(root.querySelectorAll('[data-carousel-dot]'));
    const autoplayDelay = Number(root.dataset.carouselAutoplay ?? 0);

    const slideStep = () => {
        const first = track.firstElementChild;

        if (! first) {
            return track.clientWidth;
        }

        const gap = Number.parseFloat(getComputedStyle(track).columnGap) || 0;

        return first.getBoundingClientRect().width + gap;
    };

    const maxScroll = () => track.scrollWidth - track.clientWidth;
    const activeIndex = () => Math.round(track.scrollLeft / slideStep());

    const scrollToIndex = (index) => {
        track.scrollTo({ left: index * slideStep(), behavior: 'smooth' });
    };

    const sync = () => {
        previousButton?.toggleAttribute('disabled', track.scrollLeft <= 1);
        nextButton?.toggleAttribute('disabled', track.scrollLeft >= maxScroll() - 1);

        const index = activeIndex();
        dots.forEach((dot, dotIndex) => dot.setAttribute('aria-current', String(dotIndex === index)));
    };

    previousButton?.addEventListener('click', () => scrollToIndex(Math.max(activeIndex() - 1, 0)));
    nextButton?.addEventListener('click', () => scrollToIndex(activeIndex() + 1));

    dots.forEach((dot) => dot.addEventListener('click', () => {
        scrollToIndex(Number(dot.dataset.carouselDot));
    }));

    let ticking = false;

    track.addEventListener('scroll', () => {
        if (ticking) {
            return;
        }

        ticking = true;
        requestAnimationFrame(() => {
            sync();
            ticking = false;
        });
    });

    window.addEventListener('resize', sync);
    sync();

    if (autoplayDelay > 0 && ! prefersReducedMotion) {
        let timer = null;

        const advance = () => {
            scrollToIndex(track.scrollLeft >= maxScroll() - 1 ? 0 : activeIndex() + 1);
        };

        const start = () => {
            timer ??= window.setInterval(advance, autoplayDelay);
        };

        const stop = () => {
            window.clearInterval(timer);
            timer = null;
        };

        root.addEventListener('pointerenter', stop);
        root.addEventListener('pointerleave', start);
        root.addEventListener('focusin', stop);
        root.addEventListener('focusout', start);
        document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));

        start();
    }
}

document.querySelectorAll('[data-carousel]').forEach(initCarousel);
