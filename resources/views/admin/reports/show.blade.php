<x-admin-panel title="Tinjau Laporan">
    <nav class="mb-4 text-sm text-slate-500">
        <a href="{{ route('admin.reports.index') }}" class="hover:text-brand-700">&larr; Kembali ke daftar laporan</a>
    </nav>

    <div class="card p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-slate-900">{{ $report->jobVacancy->title }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ $report->jobVacancy->company->name }}</p>
            </div>
            <div class="flex flex-col items-end gap-2">
                <x-badge :status="$report->status" />
                <x-badge :status="$report->jobVacancy->status" />
            </div>
        </div>

        <dl class="mt-5 grid gap-4 border-y border-slate-200 py-5 sm:grid-cols-2">
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Alasan laporan</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $report->reason->label() }}</dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Pelapor</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $report->reporter->name }}</dd>
                <dd class="text-sm text-slate-600">
                    {{ $report->reporter->role->label() }} · {{ $report->created_at->translatedFormat('d F Y') }}
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Keterangan pelapor</dt>
                <dd class="mt-1 text-sm text-slate-700">{{ $report->description ?? '—' }}</dd>
            </div>
        </dl>

        @if ($otherReports->isNotEmpty())
            <section class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-900">
                    Lowongan ini juga dilaporkan oleh {{ $otherReports->count() }} pengguna lain
                </p>
                <ul class="mt-2 space-y-1 text-sm text-amber-800">
                    @foreach ($otherReports as $other)
                        <li>&bull; {{ $other->reporter->name }} — {{ $other->reason->label() }}
                            ({{ $other->status->label() }})</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="mt-5 space-y-5 border-t border-slate-200 pt-5">
            <div>
                <h2 class="font-semibold text-slate-900">Deskripsi lowongan</h2>
                <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ $report->jobVacancy->description }}</p>
            </div>
            <div>
                <h2 class="font-semibold text-slate-900">Persyaratan</h2>
                <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ $report->jobVacancy->requirements }}</p>
            </div>
            <div>
                <h2 class="font-semibold text-slate-900">Cara melamar</h2>
                <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ $report->jobVacancy->how_to_apply }}</p>
            </div>
        </section>
    </div>

    @if ($report->status === App\Enums\ReportStatus::Pending)
        <div class="mt-5 grid gap-5 lg:grid-cols-2">
            <form method="POST" action="{{ route('admin.reports.uphold', $report) }}" class="card p-5">
                @csrf
                <h2 class="font-bold text-slate-900">Laporan terbukti</h2>
                <p class="mt-1 text-sm text-slate-600">Lowongan akan diturunkan dan hilang dari katalog.</p>
                <label for="uphold_note" class="label mt-3">Catatan admin (opsional)</label>
                <textarea id="uphold_note" name="admin_note" rows="3" class="input"></textarea>
                <button type="submit" class="btn-danger mt-4">Turunkan Lowongan</button>

                <p class="mt-4 text-xs text-slate-500">
                    Untuk pelanggaran berat, tangguhkan perusahaannya dari
                    <a href="{{ route('admin.companies.show', $report->jobVacancy->company) }}"
                       class="font-semibold text-brand-700 hover:underline">halaman perusahaan</a>.
                </p>
            </form>

            <form method="POST" action="{{ route('admin.reports.dismiss', $report) }}" class="card p-5">
                @csrf
                <h2 class="font-bold text-slate-900">Laporan tidak terbukti</h2>
                <p class="mt-1 text-sm text-slate-600">Lowongan tetap tayang seperti semula.</p>
                <label for="dismiss_note" class="label mt-3">Catatan admin (opsional)</label>
                <textarea id="dismiss_note" name="admin_note" rows="3" class="input"></textarea>
                <button type="submit" class="btn-secondary mt-4">Tandai Tidak Terbukti</button>
            </form>
        </div>
    @else
        <div class="card mt-5 p-5">
            <h2 class="font-bold text-slate-900">Hasil tinjauan</h2>
            <p class="mt-2 text-sm text-slate-700">{{ $report->admin_note ?? 'Tanpa catatan.' }}</p>
            <p class="mt-2 text-xs text-slate-500">
                Ditinjau {{ $report->reviewer?->name ?? 'admin' }} ·
                {{ $report->reviewed_at?->translatedFormat('d F Y H:i') }}
            </p>
        </div>
    @endif
</x-admin-panel>
