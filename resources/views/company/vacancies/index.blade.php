<x-company-panel title="Lowongan Saya" heading="Lowongan Saya">
    @if ($vacancies->isEmpty())
        <x-empty message="Anda belum memasang lowongan.">
            <a href="{{ route('company.vacancies.create') }}" class="btn-primary">Pasang Lowongan</a>
        </x-empty>
    @else
        <div class="space-y-3">
            @foreach ($vacancies as $vacancy)
                <article class="card p-4 sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-slate-900">
                                <a href="{{ route('company.vacancies.show', $vacancy) }}" class="hover:text-brand-700">
                                    {{ $vacancy->title }}
                                </a>
                            </h2>
                            <p class="mt-0.5 text-sm text-slate-600">
                                {{ $vacancy->category->name }} ·
                                {{ $vacancy->employment_type->label() }} ·
                                {{ $vacancy->is_remote ? 'Remote' : $vacancy->location }}
                            </p>
                        </div>
                        <x-badge :status="$vacancy->status" />
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                        <span>{{ $vacancy->applications_count }} pelamar</span>
                        @if ($vacancy->expires_at)
                            <span>Berlaku hingga {{ $vacancy->expires_at->translatedFormat('d F Y') }}</span>
                        @endif
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('company.applicants.index', ['vacancy' => $vacancy->id]) }}"
                           class="btn-secondary">Pelamar</a>

                        @can('update', $vacancy)
                            <a href="{{ route('company.vacancies.edit', $vacancy) }}" class="btn-secondary">Ubah</a>
                        @endcan

                        @if ($vacancy->status === App\Enums\VacancyStatus::Rejected)
                            <form method="POST" action="{{ route('company.vacancies.resubmit', $vacancy) }}">
                                @csrf
                                <button type="submit" class="btn-primary">Ajukan Ulang</button>
                            </form>
                        @endif

                        @if ($vacancy->status === App\Enums\VacancyStatus::Active)
                            <form method="POST" action="{{ route('company.vacancies.close', $vacancy) }}">
                                @csrf
                                <button type="submit" class="btn-secondary">Tutup</button>
                            </form>
                        @endif

                        @if (in_array($vacancy->status, [App\Enums\VacancyStatus::Expired, App\Enums\VacancyStatus::Closed], true))
                            <form method="POST" action="{{ route('company.vacancies.renew', $vacancy) }}">
                                @csrf
                                <button type="submit" class="btn-primary">Perpanjang</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $vacancies->links() }}</div>
    @endif
</x-company-panel>
