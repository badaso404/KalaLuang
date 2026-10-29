<x-layout wide>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">
                {{ $company?->name ?? auth()->user()->name }}
            </h1>
            <p class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                Akun perusahaan
                @if ($company)
                    <x-badge :status="$company->verification_status" />
                @endif
            </p>
        </div>

        @if ($company?->isVerified())
            <a href="{{ route('company.vacancies.create') }}" class="btn-primary">Pasang Lowongan</a>
        @endif
    </div>

    @if ($company === null)
        <div class="mt-6">
            <x-empty message="Profil perusahaan Anda belum dibuat. Lengkapi profil untuk mulai memasang lowongan.">
                <a href="{{ route('company.profile.edit') }}" class="btn-primary">Lengkapi Profil Perusahaan</a>
            </x-empty>
        </div>
    @else
        @unless ($company->isVerified())
            <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <p class="font-semibold">Perusahaan Anda belum terverifikasi.</p>
                <p class="mt-1">
                    Hanya perusahaan terverifikasi yang boleh memasang lowongan.
                    <a href="{{ route('company.verification.show') }}" class="font-semibold underline">Cek status verifikasi</a>.
                </p>
            </div>
        @endunless

        <div class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-stat label="Lowongan tayang" :value="$activeCount" :href="route('company.vacancies.index')" />
            <x-stat label="Menunggu proses" :value="$underReviewCount" hint="Kurasi atau pembayaran"
                    :href="route('company.vacancies.index')" />
            <x-stat label="Tagihan belum dibayar" :value="$unpaidCount" :href="route('company.dashboard')" />
            <x-stat label="Pelamar baru" :value="$newApplicantCount" hint="Belum ditinjau"
                    :href="route('company.applicants.index')" />
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['Dasbor lengkap', 'Ringkasan lowongan, tagihan, dan pelamar terbaru.', route('company.dashboard')],
                ['Kelola lowongan', 'Perpanjang, tutup, atau ajukan ulang lowongan Anda.', route('company.vacancies.index')],
                ['Daftar pelamar', 'Tinjau CV dan ubah status setiap lamaran.', route('company.applicants.index')],
                ['Profil perusahaan', 'Perbarui data, logo, dan deskripsi perusahaan.', route('company.profile.edit')],
                ['Status verifikasi', 'Unggah atau perbaiki dokumen legalitas.', route('company.verification.show')],
                ['Katalog publik', 'Lihat tampilan lowongan Anda dari sisi pelamar.', route('vacancies.index')],
            ] as [$title, $body, $url])
                <a href="{{ $url }}" class="card p-5 transition hover:border-brand-200 hover:shadow-md">
                    <p class="font-semibold text-slate-900">{{ $title }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $body }}</p>
                </a>
            @endforeach
        </div>
    @endif
</x-layout>
