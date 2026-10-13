<x-layout wide>
    <section class="card overflow-hidden">
        <div class="bg-brand-600 px-5 py-10 text-white sm:px-10 sm:py-14">
            <p class="text-sm font-semibold text-brand-100">Khusus fresh graduate</p>
            <h1 class="mt-2 text-2xl font-bold sm:text-4xl">Mulai karier pertama Anda di sini</h1>
            <p class="mt-3 max-w-2xl text-sm text-brand-50 sm:text-base">
                KalaLuang hanya memuat lowongan entry-level dari perusahaan yang sudah diverifikasi admin.
                Tidak ada lowongan yang mensyaratkan pengalaman kerja, dan tidak ada biaya bagi pelamar.
            </p>

            <form method="GET" action="{{ route('vacancies.index') }}" class="mt-6 flex flex-col gap-2 sm:flex-row">
                <label for="q" class="sr-only">Cari lowongan</label>
                <input id="q" name="q" type="search" class="input sm:flex-1"
                       placeholder="Cari posisi, mis. administrasi">
                <label for="location" class="sr-only">Lokasi</label>
                <input id="location" name="location" type="text" class="input sm:max-w-48" placeholder="Lokasi">
                <button type="submit" class="btn bg-accent-500 text-white hover:bg-accent-600">Cari Lowongan</button>
            </form>

            <p class="mt-4 text-sm text-brand-100">{{ $publishedCount }} lowongan sedang tayang</p>
        </div>
    </section>

    <section class="mt-8">
        <h2 class="text-lg font-bold text-slate-900">Telusuri per bidang</h2>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($categories as $category)
                <a href="{{ route('vacancies.index', ['category' => $category->slug]) }}"
                   class="card p-4 transition hover:border-brand-200 hover:shadow-md">
                    <p class="text-sm font-semibold text-slate-900">{{ $category->name }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $category->published_count }} lowongan</p>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mt-8 grid gap-4 sm:grid-cols-3">
        <div class="card p-5">
            <p class="font-semibold text-slate-900">1. Lengkapi profil</p>
            <p class="mt-1 text-sm text-slate-600">Isi pendidikan, keahlian, dan unggah CV. Profil lengkap jadi syarat melamar.</p>
        </div>
        <div class="card p-5">
            <p class="font-semibold text-slate-900">2. Cari lowongan</p>
            <p class="mt-1 text-sm text-slate-600">Filter berdasarkan jenis pekerjaan, bidang, dan lokasi. Semua tanpa pengalaman.</p>
        </div>
        <div class="card p-5">
            <p class="font-semibold text-slate-900">3. Lamar & pantau</p>
            <p class="mt-1 text-sm text-slate-600">Kirim lamaran sekali klik, lalu pantau statusnya dari halaman Lamaran.</p>
        </div>
    </section>

    <section class="mt-8">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-slate-900">Lowongan terbaru</h2>
            <a href="{{ route('vacancies.index') }}" class="text-sm font-semibold text-brand-700 hover:underline">
                Lihat semua
            </a>
        </div>

        @if ($latestVacancies->isEmpty())
            <div class="mt-4">
                <x-empty message="Belum ada lowongan yang tayang saat ini." />
            </div>
        @else
            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                @foreach ($latestVacancies as $vacancy)
                    <x-vacancy-card :$vacancy />
                @endforeach
            </div>
        @endif
    </section>

    @guest
        <section class="card mt-8 p-5 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900">Anda perusahaan yang mencari lulusan baru?</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-600">
                Daftar sebagai perusahaan, unggah dokumen legalitas untuk diverifikasi, lalu pasang lowongan
                seharga Rp{{ number_format(config('kalaluang.listing_price'), 0, ',', '.') }} per lowongan
                untuk masa tayang {{ config('kalaluang.listing_duration_days') }} hari.
            </p>
            <a href="{{ route('register', ['peran' => 'company']) }}" class="btn-primary mt-4">Daftar sebagai Perusahaan</a>
        </section>
    @endguest
</x-layout>
