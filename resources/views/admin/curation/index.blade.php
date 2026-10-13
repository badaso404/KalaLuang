<x-admin-panel title="Kurasi Lowongan" heading="Kurasi Lowongan">
    <x-status-filter :action="route('admin.curation.index')" :statuses="$statuses"
                     :selected="$filters['status'] ?? null" all-label="Semua lowongan" />

    @if ($vacancies->isEmpty())
        <x-empty message="Tidak ada lowongan dengan status ini." />
    @else
        <div class="space-y-3">
            @foreach ($vacancies as $vacancy)
                <article class="card p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-slate-900">{{ $vacancy->title }}</h2>
                            <p class="mt-0.5 text-sm text-slate-600">
                                {{ $vacancy->company->name }} · {{ $vacancy->category->name }} ·
                                {{ $vacancy->employment_type->label() }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $vacancy->is_remote ? 'Remote' : $vacancy->location }} ·
                                Min. {{ $vacancy->min_education->label() }}
                            </p>
                        </div>
                        <x-badge :status="$vacancy->status" />
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('admin.curation.show', $vacancy) }}" class="btn-secondary">Tinjau</a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $vacancies->links() }}</div>
    @endif
</x-admin-panel>
