@php
    $pledges = [
        'Legalitas perusahaan diperiksa admin (NIB wajib)',
        'Setiap lowongan dikurasi manual sebelum tayang',
        'Tidak ada lowongan yang mensyaratkan pengalaman',
        'Pelamar tidak dipungut biaya apa pun',
    ];

    $popularFilters = [
        ['label' => 'Magang', 'params' => ['employment_type' => 'internship']],
        ['label' => 'Paruh waktu', 'params' => ['employment_type' => 'part_time']],
        ['label' => 'Remote', 'params' => ['location' => 'remote']],
        ['label' => 'Administrasi', 'params' => ['category' => 'administrasi']],
        ['label' => 'Teknologi Informasi', 'params' => ['category' => 'teknologi-informasi']],
    ];
@endphp

<x-layout bleed>
    {{-- Hero: pernyataan posisi + pencarian cepat + kartu jaminan. --}}
    <section class="relative overflow-hidden bg-brand-700 text-white">
        <div aria-hidden="true"
             class="pointer-events-none absolute -top-24 -right-24 size-96 rounded-full border-[28px] border-white/10"></div>
        <div aria-hidden="true"
             class="pointer-events-none absolute -bottom-32 -left-20 size-80 rounded-full bg-accent-500/20"></div>

        <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1.15fr_0.85fr] lg:py-20">
            <div>
                <p class="badge bg-white/15 text-brand-50">
                    Khusus lulusan baru D3 &middot; D4 &middot; S1
                </p>

                <h1 class="mt-4 text-3xl font-bold tracking-tight sm:text-5xl">
                    Kerja pertama tanpa jebakan lowongan fiktif
                </h1>

                <p class="mt-4 max-w-xl text-base text-brand-50 sm:text-lg">
                    KalaLuang memuat {{ number_format($publishedCount, 0, ',', '.') }} lowongan entry-level dari
                    perusahaan yang legalitasnya sudah diverifikasi. Semuanya menerima pelamar tanpa pengalaman
                    kerja, dan gratis untuk Anda.
                </p>

                <form method="GET" action="{{ route('vacancies.index') }}"
                      class="mt-7 rounded-2xl bg-white p-3 shadow-xl sm:p-4">
                    <div class="grid gap-2 sm:grid-cols-[1.4fr_1fr_1fr_auto]">
                        <div>
                            <label for="q" class="sr-only">Cari lowongan</label>
                            <input id="q" name="q" type="search" class="input border-slate-200"
                                   placeholder="Posisi atau perusahaan">
                        </div>

                        <div>
                            <label for="hero-category" class="sr-only">Bidang</label>
                            <select id="hero-category" name="category" class="input border-slate-200">
                                <option value="">Semua bidang</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->slug }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="hero-location" class="sr-only">Lokasi</label>
                            <input id="hero-location" name="location" type="text" class="input border-slate-200"
                                   placeholder="Kota atau remote">
                        </div>

                        <button type="submit" class="btn bg-accent-500 text-white hover:bg-accent-600">
                            Cari Lowongan
                        </button>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2 px-1">
                        <span class="text-xs font-semibold text-slate-500">Populer:</span>
                        @foreach ($popularFilters as $filter)
                            <a href="{{ route('vacancies.index', $filter['params']) }}"
                               class="badge bg-slate-100 text-slate-700 transition hover:bg-brand-50 hover:text-brand-700">
                                {{ $filter['label'] }}
                            </a>
                        @endforeach
                    </div>
                </form>
            </div>

            <div class="lg:pl-4">
                <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-accent-500">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" class="size-6" aria-hidden="true">
                                <path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3Z" />
                                <path d="m9 12 2 2 4-4" />
                            </svg>
                        </span>
                        <div>
                            <p class="font-bold">Janji KalaLuang</p>
                            <p class="text-xs text-brand-100">Empat hal yang kami tegakkan di setiap lowongan</p>
                        </div>
                    </div>

                    <ul class="mt-5 space-y-3">
                        @foreach ($pledges as $pledge)
                            <li class="flex gap-2.5 text-sm text-brand-50">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                     stroke-linecap="round" stroke-linejoin="round"
                                     class="mt-0.5 size-4 shrink-0 text-accent-200" aria-hidden="true">
                                    <path d="m5 13 4 4L19 7" />
                                </svg>
                                {{ $pledge }}
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-5 flex gap-2 border-t border-white/20 pt-5">
                        <a href="{{ route('register') }}" class="btn flex-1 bg-white text-brand-700 hover:bg-brand-50">
                            Daftar Gratis
                        </a>
                        <a href="{{ route('vacancies.index') }}"
                           class="btn flex-1 border border-white/40 text-white hover:bg-white/10">
                            Lihat Lowongan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Angka hidup dari basis data, bukan klaim pemasaran. --}}
    <section class="border-b border-slate-200 bg-white">
        <dl class="mx-auto grid max-w-7xl grid-cols-2 divide-slate-200 px-4 sm:px-6 lg:grid-cols-4 lg:divide-x">
            @foreach ([
                ['Lowongan tayang', number_format($publishedCount, 0, ',', '.'), 'Aktif & dalam masa tayang'],
                ['Perusahaan terverifikasi', number_format($verifiedCompanyCount, 0, ',', '.'), 'Lolos pemeriksaan dokumen'],
                ['Bidang pekerjaan', number_format($categoryCount, 0, ',', '.'), 'Dari administrasi sampai pertanian'],
                ['Biaya untuk pelamar', 'Rp0', 'Selamanya, tanpa syarat'],
            ] as [$label, $value, $hint])
                <div class="px-0 py-6 lg:px-6">
                    <dd class="text-2xl font-bold text-brand-700 sm:text-3xl">{{ $value }}</dd>
                    <dt class="mt-1 text-sm font-semibold text-slate-900">{{ $label }}</dt>
                    <p class="text-xs text-slate-500">{{ $hint }}</p>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- Kartu iklan/promosi yang berputar otomatis. --}}
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:py-14">
        <x-promo-slider :$promos />
    </div>

    {{-- Telusuri per bidang. --}}
    <section class="bg-white py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">Telusuri {{ $categoryCount }} bidang pekerjaan</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Pilih bidang yang paling dekat dengan jurusan atau minat Anda.
                    </p>
                </div>
                <a href="{{ route('vacancies.index') }}" class="shrink-0 text-sm font-semibold text-brand-700 hover:underline">
                    Semua lowongan
                </a>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($categories as $category)
                    <a href="{{ route('vacancies.index', ['category' => $category->slug]) }}"
                       class="card group flex flex-col gap-3 p-4 transition hover:border-brand-200 hover:shadow-md">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700 transition group-hover:bg-brand-600 group-hover:text-white">
                            <x-category-icon :slug="$category->slug" />
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">{{ $category->name }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500">
                                @if ($category->published_count > 0)
                                    {{ $category->published_count }} lowongan tayang
                                @else
                                    Menunggu lowongan baru
                                @endif
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Dua baris kartu lowongan yang bisa digeser. --}}
    <div class="mx-auto max-w-7xl space-y-12 px-4 py-12 sm:px-6 lg:py-16">
        @if ($latestVacancies->isNotEmpty())
            <x-card-slider heading="Lowongan terbaru yang lolos kurasi"
                           subheading="Diurutkan dari yang paling baru tayang."
                           :href="route('vacancies.index')">
                @foreach ($latestVacancies as $vacancy)
                    <div class="w-[19rem] shrink-0 snap-start sm:w-[22rem]">
                        <x-vacancy-card :$vacancy class="h-full" />
                    </div>
                @endforeach
            </x-card-slider>
        @endif

        @if ($entryProgramVacancies->isNotEmpty())
            <x-card-slider heading="Magang dan paruh waktu"
                           subheading="Cocok untuk mahasiswa tingkat akhir atau yang ingin mulai bertahap."
                           :href="route('vacancies.index', ['employment_type' => 'internship'])"
                           link-label="Lihat semua magang">
                @foreach ($entryProgramVacancies as $vacancy)
                    <div class="w-[19rem] shrink-0 snap-start sm:w-[22rem]">
                        <x-vacancy-card :$vacancy class="h-full" />
                    </div>
                @endforeach
            </x-card-slider>
        @endif

        @if ($latestVacancies->isEmpty() && $entryProgramVacancies->isEmpty())
            <x-empty message="Belum ada lowongan yang tayang saat ini. Silakan cek kembali beberapa hari lagi.">
                <a href="{{ route('register') }}" class="btn-primary">Daftar supaya tidak ketinggalan</a>
            </x-empty>
        @endif
    </div>

    {{--
        Pembeda utama KalaLuang: saringan tiga lapis, ditampilkan dengan angka
        nyata dari basis data agar klaimnya bisa ditelusuri.
    --}}
    <section class="bg-brand-900 py-14 text-white lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="max-w-2xl">
                <p class="badge bg-accent-500/20 text-accent-200">Yang membedakan kami</p>
                <h2 class="mt-4 text-2xl font-bold sm:text-3xl">Saringan tiga lapis sebelum sebuah lowongan sampai ke Anda</h2>
                <p class="mt-3 text-sm text-brand-100 sm:text-base">
                    Portal lowongan umum menayangkan apa pun yang diunggah perusahaan. Di KalaLuang, satu lowongan
                    harus melewati tiga pemeriksaan — dan lapis ketiga tetap berjalan setelah lowongan tayang.
                </p>
            </div>

            <ol class="mt-10 grid gap-5 lg:grid-cols-3">
                @foreach ([
                    [
                        'step' => 'Lapis 1',
                        'title' => 'Verifikasi legalitas perusahaan',
                        'body' => 'Perusahaan wajib mengunggah NIB. Admin memeriksa dokumen secara manual; yang gagal tidak bisa memasang lowongan sama sekali.',
                        'metrics' => [
                            [number_format($companySubmissionCount, 0, ',', '.'), 'pengajuan masuk'],
                            [number_format($verifiedCompanyCount, 0, ',', '.'), 'terverifikasi'],
                            [number_format($rejectedCompanyCount, 0, ',', '.'), 'ditolak / ditangguhkan'],
                        ],
                    ],
                    [
                        'step' => 'Lapis 2',
                        'title' => 'Kurasi isi lowongan',
                        'body' => 'Setiap lowongan dibaca admin: tidak boleh mensyaratkan pengalaman, tidak boleh memungut biaya dari pelamar, dan deskripsinya harus jelas.',
                        'metrics' => [
                            [number_format($curatedVacancyCount, 0, ',', '.'), 'lolos kurasi'],
                            [number_format($rejectedVacancyCount, 0, ',', '.'), 'dikembalikan untuk revisi'],
                            ['Rp0', 'biaya dari pelamar'],
                        ],
                    ],
                    [
                        'step' => 'Lapis 3',
                        'title' => 'Pengawasan setelah tayang',
                        'body' => 'Pengguna bisa melaporkan lowongan yang mencurigakan. Laporan yang terbukti membuat lowongan diturunkan, dan perusahaannya bisa ditangguhkan.',
                        'metrics' => [
                            [number_format($reviewedReportCount, 0, ',', '.'), 'laporan ditinjau'],
                            [number_format($takenDownVacancyCount, 0, ',', '.'), 'lowongan diturunkan'],
                            ['5', 'kategori alasan laporan'],
                        ],
                    ],
                ] as $layer)
                    <li class="rounded-2xl border border-white/15 bg-white/5 p-6">
                        <p class="text-xs font-semibold tracking-widest uppercase text-accent-200">{{ $layer['step'] }}</p>
                        <h3 class="mt-2 text-lg font-bold">{{ $layer['title'] }}</h3>
                        <p class="mt-2 text-sm text-brand-100">{{ $layer['body'] }}</p>

                        <dl class="mt-5 space-y-2 border-t border-white/15 pt-4">
                            @foreach ($layer['metrics'] as [$value, $label])
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="text-sm text-brand-100">{{ $label }}</dt>
                                    <dd class="text-lg font-bold">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </li>
                @endforeach
            </ol>

            <p class="mt-6 text-xs text-brand-200">
                Angka di atas dihitung langsung dari basis data KalaLuang saat halaman ini dimuat.
            </p>
        </div>
    </section>

    {{-- Perbandingan posisi KalaLuang terhadap portal lowongan umum. --}}
    <section class="bg-white py-12 lg:py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6">
            <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">Kenapa tidak sekadar pakai portal lowongan umum?</h2>
            <p class="mt-1 text-sm text-slate-600">
                Portal besar melayani semua level pengalaman. Lulusan baru sering tenggelam di antaranya.
            </p>

            <div class="card mt-6 overflow-x-auto">
                <table class="w-full min-w-[40rem] text-left text-sm">
                    <caption class="sr-only">Perbandingan KalaLuang dengan portal lowongan umum</caption>
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold">Aspek</th>
                            <th scope="col" class="px-4 py-3 font-semibold text-brand-700">KalaLuang</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Portal lowongan umum</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ([
                            ['Syarat pengalaman', 'Dilarang — lowongan yang meminta pengalaman ditolak saat kurasi', 'Banyak lowongan berlabel entry-level tetap meminta 1–2 tahun pengalaman'],
                            ['Keaslian perusahaan', 'Dokumen legalitas (NIB) diperiksa admin sebelum boleh memasang lowongan', 'Umumnya cukup mendaftar dengan email perusahaan'],
                            ['Biaya bagi pelamar', 'Rp0 untuk mendaftar, melamar, dan menyimpan lowongan', 'Sebagian lowongan menyelipkan biaya pelatihan atau administrasi'],
                            ['Transparansi gaji', 'Rentang gaji ditampilkan langsung di kartu lowongan bila diisi', 'Sering hanya ditulis "gaji kompetitif"'],
                            ['Lowongan bermasalah', 'Dilaporkan pengguna, ditinjau admin, lalu diturunkan dari katalog', 'Pelaporan ada, tapi tindak lanjutnya sulit ditelusuri pelamar'],
                            ['Sasaran pengguna', 'Hanya lulusan baru D3/D4/S1 — seluruh katalog relevan', 'Semua level, dari fresh graduate sampai manajer senior'],
                        ] as [$aspect, $kalaluang, $others])
                            <tr>
                                <th scope="row" class="px-4 py-3 align-top font-semibold text-slate-900">{{ $aspect }}</th>
                                <td class="bg-brand-50/50 px-4 py-3 align-top text-slate-800">{{ $kalaluang }}</td>
                                <td class="px-4 py-3 align-top text-slate-600">{{ $others }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Perusahaan terverifikasi yang sedang membuka lowongan. --}}
    @if ($featuredCompanies->isNotEmpty())
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:py-16">
            <x-card-slider heading="Perusahaan terverifikasi yang sedang membuka lowongan"
                           subheading="Semua logo di bawah sudah melewati pemeriksaan dokumen oleh admin."
                           :href="route('vacancies.index')" link-label="Lihat lowongannya">
                @foreach ($featuredCompanies as $company)
                    <article class="card flex w-[16rem] shrink-0 snap-start flex-col gap-3 p-5">
                        @if ($company->logo_path)
                            <img src="{{ Storage::url($company->logo_path) }}" alt=""
                                 class="size-12 rounded-xl object-cover">
                        @else
                            <span class="flex size-12 items-center justify-center rounded-xl bg-brand-50 text-lg font-bold text-brand-700">
                                {{ Str::substr($company->name, 0, 1) }}
                            </span>
                        @endif

                        <div>
                            <p class="font-semibold text-slate-900">{{ $company->name }}</p>
                            <p class="text-sm text-slate-600">{{ $company->industry }} &middot; {{ $company->city }}</p>
                        </div>

                        <div class="mt-auto flex flex-wrap items-center gap-2">
                            <x-badge classes="bg-brand-100 text-brand-700">&check; Terverifikasi</x-badge>
                            <span class="text-xs text-slate-500">{{ $company->published_count }} lowongan</span>
                        </div>
                    </article>
                @endforeach
            </x-card-slider>
        </div>
    @endif

    {{-- Alur singkat untuk pencari kerja. --}}
    <section class="bg-white py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">Tiga langkah sampai lamaran terkirim</h2>

            <ol class="mt-6 grid gap-4 lg:grid-cols-3">
                @foreach ([
                    ['Lengkapi profil', 'Isi pendidikan, keahlian, dan unggah CV (PDF maks 2 MB). Profil lengkap jadi syarat melamar.'],
                    ['Cari lowongan', 'Saring berdasarkan jenis pekerjaan, bidang, dan lokasi. Semuanya sudah pasti tanpa syarat pengalaman.'],
                    ['Lamar & pantau', 'Kirim lamaran sekali klik, lalu pantau statusnya: Baru, Diproses, Diterima, atau Ditolak.'],
                ] as $index => [$title, $body])
                    <li class="card flex gap-4 p-5">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-600 font-bold text-white">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <p class="font-semibold text-slate-900">{{ $title }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $body }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Penawaran untuk perusahaan. --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:py-16">
        <div class="card grid gap-8 p-6 sm:p-10 lg:grid-cols-[1.1fr_0.9fr]">
            <div>
                <p class="badge bg-accent-50 text-accent-700">Untuk perusahaan</p>
                <h2 class="mt-4 text-xl font-bold text-slate-900 sm:text-2xl">
                    Cari lulusan baru yang memang siap dibina
                </h2>
                <p class="mt-3 text-sm text-slate-600">
                    Pelamar di KalaLuang sudah mengisi pendidikan, keahlian, dan CV sebelum bisa menekan tombol
                    lamar. Anda juga mendapat lencana Terverifikasi setelah dokumen legalitas lolos pemeriksaan —
                    pembeda yang langsung terlihat pelamar di setiap kartu lowongan.
                </p>

                <ul class="mt-5 grid gap-2 sm:grid-cols-2">
                    @foreach ([
                        'Lencana Terverifikasi di tiap lowongan',
                        'Dasbor pelamar dengan status lamaran',
                        'Unduh CV pelamar langsung dari dasbor',
                        'Tanpa paket langganan atau komisi',
                    ] as $benefit)
                        <li class="flex gap-2 text-sm text-slate-700">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 class="mt-0.5 size-4 shrink-0 text-brand-600" aria-hidden="true">
                                <path d="m5 13 4 4L19 7" />
                            </svg>
                            {{ $benefit }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-2xl bg-slate-50 p-6">
                <p class="text-sm font-semibold text-slate-600">Tarif tunggal</p>
                <p class="mt-1 flex items-baseline gap-1">
                    <span class="text-3xl font-bold text-slate-900">
                        Rp{{ number_format(config('kalaluang.listing_price'), 0, ',', '.') }}
                    </span>
                    <span class="text-sm text-slate-600">/ lowongan</span>
                </p>
                <p class="mt-1 text-sm text-slate-600">
                    Masa tayang {{ config('kalaluang.listing_duration_days') }} hari, bisa diperpanjang.
                </p>

                <ol class="mt-5 space-y-3 text-sm text-slate-700">
                    @foreach ([
                        'Daftar & lengkapi profil perusahaan',
                        'Unggah NIB untuk diverifikasi admin',
                        'Pasang lowongan & bayar tagihan',
                        'Lowongan tayang setelah lolos kurasi',
                    ] as $index => $step)
                        <li class="flex gap-3">
                            <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-brand-700 ring-1 ring-slate-200">
                                {{ $index + 1 }}
                            </span>
                            {{ $step }}
                        </li>
                    @endforeach
                </ol>

                <a href="{{ route('register', ['peran' => 'company']) }}" class="btn-primary mt-6 w-full">
                    Daftar sebagai Perusahaan
                </a>
            </div>
        </div>
    </section>

    {{-- Pertanyaan yang sering muncul. --}}
    <section class="bg-white py-12 lg:py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">Pertanyaan yang sering diajukan</h2>

            <div class="mt-6 divide-y divide-slate-200">
                @foreach ([
                    ['Apakah benar-benar gratis untuk pencari kerja?', 'Ya. Mendaftar, melengkapi profil, menyimpan lowongan, dan melamar tidak dikenakan biaya. Pendapatan KalaLuang hanya berasal dari biaya pemasangan lowongan yang dibayar perusahaan.'],
                    ['Saya belum punya pengalaman kerja sama sekali, apakah bisa melamar?', 'Justru itu sasaran kami. Seluruh lowongan di KalaLuang wajib menerima pelamar tanpa pengalaman kerja formal. Lowongan yang mensyaratkan pengalaman ditolak saat kurasi admin.'],
                    ['Apa syarat sebelum tombol "Lamar Sekarang" bisa dipakai?', 'Profil Anda harus lengkap: data pendidikan (jenjang, institusi, jurusan) terisi dan CV sudah diunggah. Email akun juga perlu diverifikasi.'],
                    ['Bagaimana kalau saya menemukan lowongan yang mencurigakan?', 'Buka detail lowongan lalu klik "Laporkan lowongan", pilih alasannya, dan tambahkan keterangan. Admin meninjau laporan tersebut; bila terbukti, lowongan diturunkan dari katalog.'],
                    ['Berapa lama lowongan tayang di KalaLuang?', config('kalaluang.listing_duration_days').' hari sejak lolos kurasi. Setelah itu lowongan otomatis kedaluwarsa, dan perusahaan dapat memperpanjangnya.'],
                ] as $faq)
                    <details class="group py-4">
                        <summary class="flex cursor-pointer list-none items-start justify-between gap-4 font-semibold text-slate-900">
                            {{ $faq[0] }}
                            <span class="mt-1 shrink-0 text-brand-600 transition group-open:rotate-45" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" class="size-4">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                            </span>
                        </summary>
                        <p class="mt-2 pr-8 text-sm text-slate-600">{{ $faq[1] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Penutup: dua jalur pendaftaran. --}}
    <section class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:pb-20">
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl bg-brand-700 p-6 text-white sm:p-8">
                <h2 class="text-lg font-bold sm:text-xl">Saya mencari kerja pertama</h2>
                <p class="mt-2 text-sm text-brand-50">
                    Buat akun, lengkapi profil sekali, lalu melamar ke berapa pun lowongan tanpa mengunggah CV
                    berulang kali.
                </p>
                <a href="{{ route('register') }}" class="btn mt-5 bg-white text-brand-700 hover:bg-brand-50">
                    Daftar sebagai Pencari Kerja
                </a>
            </div>

            <div class="rounded-2xl bg-slate-900 p-6 text-white sm:p-8">
                <h2 class="text-lg font-bold sm:text-xl">Saya mencari lulusan baru</h2>
                <p class="mt-2 text-sm text-slate-300">
                    Verifikasi perusahaan Anda sekali, lalu pasang lowongan kapan pun dengan tarif tunggal
                    Rp{{ number_format(config('kalaluang.listing_price'), 0, ',', '.') }}.
                </p>
                <a href="{{ route('register', ['peran' => 'company']) }}"
                   class="btn mt-5 bg-accent-500 text-white hover:bg-accent-600">
                    Daftar sebagai Perusahaan
                </a>
            </div>
        </div>
    </section>
</x-layout>
