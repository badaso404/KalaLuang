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

/**
 * Pemilih keahlian pada form profil pencari kerja.
 *
 * Katalognya panjang dan dikelompokkan per sektor, jadi JS di sini hanya
 * menambahkan pencarian, penghitung jumlah pilihan, dan buka-tutup otomatis.
 * Tanpa JS, semua kelompok tetap bisa dibuka dan centangnya tetap terkirim.
 */
function initSkillPicker(root) {
    const search = root.querySelector('[data-skill-search]');
    const counter = root.querySelector('[data-skill-count]');
    const noMatch = root.querySelector('[data-skill-no-match]');
    const groups = Array.from(root.querySelectorAll('[data-skill-group]'));
    const options = Array.from(root.querySelectorAll('[data-skill-option]'));

    const checkboxOf = (option) => option.querySelector('input[type="checkbox"]');

    const syncCounts = () => {
        if (counter) {
            counter.textContent = String(options.filter((option) => checkboxOf(option).checked).length);
        }

        groups.forEach((group) => {
            const badge = group.querySelector('[data-skill-group-count]');

            if (! badge) {
                return;
            }

            const checked = Array.from(group.querySelectorAll('input[type="checkbox"]'))
                .filter((input) => input.checked).length;

            badge.textContent = `${checked} dipilih`;
            badge.style.display = checked > 0 ? '' : 'none';
        });
    };

    const filter = () => {
        const terms = (search?.value ?? '').toLowerCase().split(/\s+/).filter(Boolean);
        let visible = 0;

        options.forEach((option) => {
            const name = option.dataset.skillName ?? '';
            const matches = terms.every((term) => name.includes(term));

            option.hidden = ! matches;
            visible += matches ? 1 : 0;
        });

        groups.forEach((group) => {
            const hasVisible = Array.from(group.querySelectorAll('[data-skill-option]'))
                .some((option) => ! option.hidden);
            const hasChecked = Array.from(group.querySelectorAll('input[type="checkbox"]'))
                .some((input) => input.checked);

            group.hidden = ! hasVisible;

            if (terms.length > 0) {
                group.open = hasVisible;
            } else {
                group.open = hasChecked;
            }
        });

        noMatch?.classList.toggle('hidden', terms.length === 0 || visible > 0);
    };

    root.addEventListener('change', (event) => {
        if (event.target.matches('input[type="checkbox"]')) {
            syncCounts();
        }
    });

    search?.addEventListener('input', filter);
    search?.addEventListener('search', filter);

    syncCounts();
}

document.querySelectorAll('[data-skill-picker]').forEach(initSkillPicker);
