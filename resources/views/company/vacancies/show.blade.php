<x-company-panel :title="$vacancy->title">
    <div class="card p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-slate-900">{{ $vacancy->title }}</h1>
                <p class="mt-1 text-sm text-slate-600">
                    {{ $vacancy->category->name }} ·
                    {{ $vacancy->employment_type->label() }} ·
                    {{ $vacancy->is_remote ? 'Remote' : $vacancy->location }} ·
                    Min. {{ $vacancy->min_education->label() }}
                </p>
            </div>
            <x-badge :status="$vacancy->status" />
        </div>

        @if ($vacancy->review_note)
            <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-semibold">Catatan kurasi admin</p>
                <p class="mt-1">{{ $vacancy->review_note }}</p>
            </div>
        @endif

        <dl class="mt-4 grid gap-4 border-t border-slate-200 pt-4 sm:grid-cols-3">
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Pelamar</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $applicationCount }}</dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Ditayangkan</dt>
                <dd class="mt-1 font-semibold text-slate-900">
                    {{ $vacancy->published_at?->translatedFormat('d F Y') ?? '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Berlaku hingga</dt>
                <dd class="mt-1 font-semibold text-slate-900">
                    {{ $vacancy->expires_at?->translatedFormat('d F Y') ?? '—' }}
                </dd>
            </div>
        </dl>

        <div class="mt-5 space-y-5 border-t border-slate-200 pt-5">
            <section>
                <h2 class="font-semibold text-slate-900">Deskripsi</h2>
                <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ $vacancy->description }}</p>
            </section>
            <section>
                <h2 class="font-semibold text-slate-900">Persyaratan</h2>
                <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ $vacancy->requirements }}</p>
            </section>
            <section>
                <h2 class="font-semibold text-slate-900">Cara melamar</h2>
                <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ $vacancy->how_to_apply }}</p>
            </section>
        </div>

        <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-200 pt-5">
            <a href="{{ route('company.applicants.index', ['vacancy' => $vacancy->id]) }}" class="btn-secondary">
                Lihat Pelamar
            </a>
            @can('update', $vacancy)
                <a href="{{ route('company.vacancies.edit', $vacancy) }}" class="btn-secondary">Ubah</a>
            @endcan
            @if ($vacancy->status === App\Enums\VacancyStatus::Active)
                <a href="{{ route('vacancies.show', $vacancy) }}" class="btn-secondary">Lihat Halaman Publik</a>
            @endif
        </div>
    </div>

    <section class="card mt-5 p-5">
        <h2 class="font-bold text-slate-900">Riwayat tagihan</h2>
        @if ($vacancy->payments->isEmpty())
            <p class="mt-3 text-sm text-slate-600">Belum ada tagihan.</p>
        @else
            <ul class="mt-3 divide-y divide-slate-100">
                @foreach ($vacancy->payments as $payment)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $payment->invoice_number }}</p>
                            <p class="text-xs text-slate-500">
                                Rp{{ number_format($payment->amount, 0, ',', '.') }} ·
                                {{ $payment->created_at->translatedFormat('d F Y') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-badge :status="$payment->status" />
                            <a href="{{ route('company.payments.show', $payment) }}"
                               class="text-sm font-semibold text-brand-700 hover:underline">Detail</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-company-panel>
