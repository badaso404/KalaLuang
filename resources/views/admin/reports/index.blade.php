<x-admin-panel title="Laporan Lowongan" heading="Laporan Lowongan">
    <x-status-filter :action="route('admin.reports.index')" :statuses="$statuses"
                     :selected="$filters['status'] ?? null" all-label="Semua laporan" />

    @if ($reports->isEmpty())
        <x-empty message="Tidak ada laporan dengan status ini." />
    @else
        <div class="space-y-3">
            @foreach ($reports as $report)
                <article class="card p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-slate-900">{{ $report->jobVacancy->title }}</h2>
                            <p class="mt-0.5 text-sm text-slate-600">{{ $report->jobVacancy->company->name }}</p>
                            <p class="mt-1 text-sm text-slate-700">
                                <span class="font-medium">Alasan:</span> {{ $report->reason->label() }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Dilaporkan {{ $report->reporter->name }} ·
                                {{ $report->created_at->translatedFormat('d F Y') }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <x-badge :status="$report->status" />
                            <x-badge :status="$report->jobVacancy->status" />
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('admin.reports.show', $report) }}" class="btn-secondary">Tinjau Laporan</a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $reports->links() }}</div>
    @endif
</x-admin-panel>
