@php
    $profile = $application->user->jobSeekerProfile;
@endphp

<x-company-panel :title="'Pelamar: '.$application->user->name">
    <nav class="mb-4 text-sm text-slate-500">
        <a href="{{ route('company.applicants.index') }}" class="hover:text-brand-700">&larr; Kembali ke daftar pelamar</a>
    </nav>

    <div class="card p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-slate-900">{{ $application->user->name }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ $application->user->email }}</p>
                <p class="mt-0.5 text-xs text-slate-500">
                    Melamar <strong>{{ $application->jobVacancy->title }}</strong> pada
                    {{ $application->created_at->translatedFormat('d F Y') }}
                </p>
            </div>
            <x-badge :status="$application->status" />
        </div>

        <dl class="mt-5 grid gap-4 border-y border-slate-200 py-5 sm:grid-cols-2">
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Pendidikan</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">
                    {{ $profile?->education_level?->label() }} {{ $profile?->major }}
                </dd>
                <dd class="text-sm text-slate-600">{{ $profile?->institution }}</dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Tahun lulus</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $profile?->graduation_year ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Domisili</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $profile?->city ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">No. HP</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $profile?->phone ?? '—' }}</dd>
            </div>
        </dl>

        @if ($profile?->skills->isNotEmpty())
            <section class="mt-5">
                <h2 class="font-semibold text-slate-900">Keahlian</h2>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($profile->skills as $skill)
                        <x-badge>{{ $skill->name }}</x-badge>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($profile?->bio)
            <section class="mt-5">
                <h2 class="font-semibold text-slate-900">Tentang pelamar</h2>
                <p class="mt-1 text-sm text-slate-700">{{ $profile->bio }}</p>
            </section>
        @endif

        @if ($application->cover_letter)
            <section class="mt-5">
                <h2 class="font-semibold text-slate-900">Surat lamaran</h2>
                <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ $application->cover_letter }}</p>
            </section>
        @endif

        <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-200 pt-5">
            @if ($application->cv_path)
                <a href="{{ route('company.applicants.cv', $application) }}" target="_blank" class="btn-secondary">Buka CV</a>
            @endif
            @if ($profile?->portfolio_url)
                <a href="{{ $profile->portfolio_url }}" target="_blank" rel="noopener nofollow" class="btn-secondary">
                    Lihat Portofolio
                </a>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('company.applicants.status', $application) }}" class="card mt-5 p-5">
        @csrf
        @method('PUT')

        <h2 class="font-bold text-slate-900">Ubah status pelamar</h2>
        <p class="mt-1 text-sm text-slate-600">Pelamar akan menerima notifikasi setiap kali status berubah.</p>

        <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto]">
            <div>
                <label for="status" class="label">Status</label>
                <select id="status" name="status" class="input" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($application->status === $status)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary w-full">Simpan Status</button>
            </div>
        </div>
    </form>
</x-company-panel>
