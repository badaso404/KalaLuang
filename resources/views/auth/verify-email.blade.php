<x-layout title="Verifikasi Email">
    <div class="mx-auto max-w-md">
        <div class="card p-5">
            <h1 class="text-xl font-bold text-slate-900">Verifikasi email Anda</h1>
            <p class="mt-2 text-sm text-slate-600">
                Kami telah mengirim tautan verifikasi ke <strong>{{ auth()->user()->email }}</strong>.
                Klik tautan tersebut untuk mengaktifkan akun Anda. Verifikasi diperlukan sebelum melamar,
                menyimpan lowongan, atau mengirim laporan.
            </p>

            <form method="POST" action="{{ route('verification.send') }}" class="mt-5">
                @csrf
                <button type="submit" class="btn-primary w-full">Kirim ulang tautan verifikasi</button>
            </form>

            @if (auth()->user()->isJobSeeker())
                <a href="{{ route('job-seeker.profile.edit') }}" class="btn-secondary mt-3 w-full">
                    Lengkapi profil dulu
                </a>
            @elseif (auth()->user()->isCompany())
                <a href="{{ route('company.profile.edit') }}" class="btn-secondary mt-3 w-full">
                    Lengkapi profil perusahaan
                </a>
            @endif

            <p class="mt-4 text-center text-xs text-slate-500">
                Di lingkungan pengembangan, email tertulis ke <code>storage/logs/laravel.log</code>.
            </p>
        </div>
    </div>
</x-layout>
