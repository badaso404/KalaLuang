<x-admin-panel title="Verifikasi Perusahaan" heading="Verifikasi Perusahaan">
    <x-status-filter :action="route('admin.companies.index')" :statuses="$statuses"
                     :selected="$filters['status'] ?? null" all-label="Semua perusahaan" />

    @if ($companies->isEmpty())
        <x-empty message="Tidak ada perusahaan dengan status ini." />
    @else
        <div class="space-y-3">
            @foreach ($companies as $company)
                <article class="card p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-slate-900">{{ $company->name }}</h2>
                            <p class="mt-0.5 text-sm text-slate-600">
                                {{ $company->industry }} · {{ $company->city }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $company->user->name }} · {{ $company->user->email }} ·
                                {{ $company->job_vacancies_count }} lowongan
                            </p>
                        </div>
                        <x-badge :status="$company->verification_status" />
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('admin.companies.show', $company) }}" class="btn-secondary">
                            Periksa Dokumen
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $companies->links() }}</div>
    @endif
</x-admin-panel>
