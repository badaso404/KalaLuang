<x-company-panel title="Pelamar" heading="Daftar Pelamar">
    <form method="GET" action="{{ route('company.applicants.index') }}" class="card mb-5 grid gap-3 p-4 sm:grid-cols-3">
        <div>
            <label for="status" class="label">Status</label>
            <select id="status" name="status" class="input">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="vacancy" class="label">Lowongan</label>
            <select id="vacancy" name="vacancy" class="input">
                <option value="">Semua lowongan</option>
                @foreach ($vacancies as $vacancy)
                    <option value="{{ $vacancy->id }}"
                        @selected((int) ($filters['vacancy'] ?? 0) === $vacancy->id)>{{ $vacancy->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primary flex-1">Filter</button>
            <a href="{{ route('company.applicants.index') }}" class="btn-secondary">Reset</a>
        </div>
    </form>

    @if ($applications->isEmpty())
        <x-empty message="Belum ada pelamar yang cocok dengan filter ini." />
    @else
        <div class="space-y-3">
            @foreach ($applications as $application)
                <article class="card p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-slate-900">{{ $application->user->name }}</h2>
                            <p class="mt-0.5 text-sm text-slate-600">
                                {{ $application->user->jobSeekerProfile?->education_level?->label() }}
                                {{ $application->user->jobSeekerProfile?->major }}
                                @if ($application->user->jobSeekerProfile?->institution)
                                    — {{ $application->user->jobSeekerProfile->institution }}
                                @endif
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Melamar {{ $application->jobVacancy->title }} ·
                                {{ $application->created_at->translatedFormat('d F Y') }}
                            </p>
                        </div>
                        <x-badge :status="$application->status" />
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('company.applicants.show', $application) }}" class="btn-secondary">
                            Lihat Detail
                        </a>
                        @if ($application->cv_path)
                            <a href="{{ Storage::url($application->cv_path) }}" target="_blank" class="btn-secondary">
                                Buka CV
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $applications->links() }}</div>
    @endif
</x-company-panel>
