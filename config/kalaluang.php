<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Biaya Pemasangan Lowongan
    |--------------------------------------------------------------------------
    |
    | Tarif tunggal yang ditagihkan ke perusahaan setiap kali memasang atau
    | memperpanjang satu lowongan, beserta masa tayangnya dalam hari.
    |
    */

    'listing_price' => (int) env('KALALUANG_LISTING_PRICE', 100_000),

    'listing_duration_days' => (int) env('KALALUANG_LISTING_DURATION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Slide Promosi Beranda Publik
    |--------------------------------------------------------------------------
    |
    | Kartu iklan yang berputar di beranda publik. Setiap slide hanya berisi
    | teks dan satu tautan ke halaman yang sudah ada, jadi tidak ada data
    | promosi yang perlu disimpan di basis data (di luar cakupan MVP).
    |
    | Nilai `theme` dipetakan ke kelas Tailwind di resources/views/components/
    | promo-slider.blade.php agar kelasnya tetap literal dan terbaca Tailwind.
    |
    | @var list<array{eyebrow: string, title: string, body: string, theme: string,
    |                 cta: array{label: string, route: string, params?: array<string, string>}}>
    |
    */

    'promos' => [
        [
            'eyebrow' => 'Kurasi manual',
            'title' => 'Tidak ada lowongan yang tayang tanpa dibaca admin',
            'body' => 'Legalitas perusahaan diperiksa, isi lowongan dikurasi satu per satu. Yang meminta pengalaman atau memungut biaya langsung ditolak.',
            'theme' => 'brand',
            'cta' => ['label' => 'Lihat lowongan terkurasi', 'route' => 'vacancies.index'],
        ],
        [
            'eyebrow' => 'Gratis selamanya',
            'title' => 'Pelamar tidak pernah kami tagih sepeser pun',
            'body' => 'Mendaftar, melamar, menyimpan lowongan, dan mengunduh CV sendiri — semuanya nol rupiah. Pendapatan KalaLuang murni dari perusahaan.',
            'theme' => 'accent',
            'cta' => ['label' => 'Buat akun pencari kerja', 'route' => 'register'],
        ],
        [
            'eyebrow' => 'Magang & paruh waktu',
            'title' => 'Masih kuliah tingkat akhir? Mulai dari magang',
            'body' => 'Program magang dan paruh waktu dari perusahaan terverifikasi, lengkap dengan rentang gaji yang ditulis terbuka.',
            'theme' => 'navy',
            'cta' => [
                'label' => 'Telusuri lowongan magang',
                'route' => 'vacancies.index',
                'params' => ['employment_type' => 'internship'],
            ],
        ],
        [
            'eyebrow' => 'Kerja remote',
            'title' => 'Kerja pertama dari kota asal, tanpa harus merantau',
            'body' => 'Filter lowongan remote dan temukan tim yang terbiasa membina lulusan baru dari jarak jauh.',
            'theme' => 'teal',
            'cta' => [
                'label' => 'Lihat lowongan remote',
                'route' => 'vacancies.index',
                'params' => ['location' => 'remote'],
            ],
        ],
        [
            'eyebrow' => 'Untuk perusahaan',
            'title' => 'Satu tarif, tanpa paket berlangganan',
            'body' => 'Rp100.000 per lowongan untuk masa tayang 30 hari. Tidak ada biaya tersembunyi, tidak ada komisi per pelamar.',
            'theme' => 'slate',
            'cta' => [
                'label' => 'Daftar sebagai perusahaan',
                'route' => 'register',
                'params' => ['peran' => 'company'],
            ],
        ],
    ],

];
