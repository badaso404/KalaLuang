@php
    use App\Enums\ApplicationStatus;
    use App\Enums\VacancyStatus;

    $tabs = collect([['label' => 'Semua', 'status' => null, 'count' => $total]])
        ->concat(collect(ApplicationStatus::cases())->map(fn (ApplicationStatus $case) => [
            'label' => $case->label(),
            'status' => $case->value,
            'count' => (int) ($statusCounts[$case->value] ?? 0),
        ]))
        ->map(fn (array $tab) => [
            'label' => $tab['label'],
            'count' => $tab['count'],
            'active' => ($filters['status'] ?? null) === $tab['status'],
            'href' => route('job-seeker.applications.index', array_filter([
                'status' => $tab['status'],
                'q' => $filters['q'] ?? null,
                'sort' => $sort === 'terbaru' ? null : $sort,
            ])),
        ])
        ->all();

    $sortLabels = [
        'terbaru' => 'Terbaru dikirim',
        'terlama' => 'Terlama dikirim',
        'pembaruan' => 'Pembaruan terakhir',
    ];
@endphp

<x-layout title="Lamaran Saya">
    <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Riwayat Lamaran</h1>
    <p class="mt-1 text-sm text-slate-600">Pantau perkembangan setiap lamaran yang Anda kirim.</p>

    @if ($total > 0)
        <div class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-stat label="Total lamaran" :value="$total" />
            <x-stat label="Menunggu ditinjau" :value="$awaitingResponse" hint="Belum dibuka perusahaan" />
            <x-stat label="Diterima" :value="$acceptedCount" />
            <x-stat label="Tingkat respons" :value="$responseRate.'%'"
                    hint="Lamaran yang sudah ditanggapi" />
        </div>
    @endif

    @if ($notifications->isNotEmpty())
        <section class="card mt-6 p-5">
            <h2 class="font-bold text-slate-900">Pembaruan terbaru</h2>
            <p class="mt-1 text-sm text-slate-600">Perubahan status yang dikirim perusahaan ke akun Anda.</p>

            <ul class="mt-4 space-y-2">
                @foreach ($notifications as $notification)
                    @php
                        $status = ApplicationStatus::tryFrom($notification->data['status'] ?? '');
                        $isNew = in_array($notification->id, $unreadNotificationIds, strict: true);
                    @endphp

                    <li @class([
                        'flex flex-wrap items-start justify-between gap-2 rounded-lg px-3 py-2.5 text-sm',
                        'bg-brand-50' => $isNew,
                        'bg-slate-50' => ! $isNew,
                    ])>
                        <div class="min-w-0">
                            <p class="text-slate-800">
                                {{ $notification->data['message'] ?? 'Status lamaran Anda diperbarui.' }}
                            </p>
                            @if (filled($notification->data['note'] ?? null))
                                <p class="mt-1 text-sm whitespace-pre-line text-slate-600">
                                    &ldquo;{{ $notification->data['note'] }}&rdquo;
                                </p>
                            @endif
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $notification->created_at->diffForHumans() }}
                                @if ($isNew)
                                    &middot; <span class="font-semibold text-brand-700">baru</span>
                                @endif
                            </p>
                        </div>
                        @if ($status)
                            <x-badge :status="$status" />
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($total > 0)
        <div class="mt-6 space-y-3">
            <x-filter-tabs :$tabs />

            <form method="GET" action="{{ route('job-seeker.applications.index') }}"
                  class="card flex flex-wrap items-end gap-3 p-4">
                @if ($filters['status'] ?? null)
                    <input type="hidden" name="status" value="{{ $filters['status'] }}">
                @endif

                <div class="min-w-48 flex-1">
                    <label for="q" class="label">Cari lamaran</label>
                    <input id="q" name="q" type="search" class="input" value="{{ $filters['q'] ?? '' }}"
                           placeholder="Posisi atau perusahaan">
                </div>

                <div class="min-w-44">
                    <label for="sort" class="label">Urutkan</label>
                    <select id="sort" name="sort" class="input">
                        @foreach ($sorts as $option)
                            <option value="{{ $option }}" @selected($sort === $option)>
                                {{ $sortLabels[$option] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn-primary">Terapkan</button>
                <a href="{{ route('job-seeker.applications.index') }}" class="btn-secondary">Reset</a>
            </form>
        </div>
    @endif

    @if ($applications->isEmpty())
        <div class="mt-6">
            @if ($total === 0)
                <x-empty message="Anda belum mengirim lamaran apa pun.">
                    <a href="{{ route('vacancies.index') }}" class="btn-primary">Cari Lowongan</a>
                </x-empty>
            @else
                <x-empty message="Tidak ada lamaran yang cocok dengan penyaringan Anda.">
                    <a href="{{ route('job-seeker.applications.index') }}" class="btn-secondary">Hapus penyaringan</a>
                </x-empty>
            @endif
        </div>
    @else
        <p class="mt-5 text-sm text-slate-600">
            Menampilkan {{ $applications->count() }} dari {{ $applications->total() }} lamaran.
        </p>

        <div class="mt-3 space-y-3">
            @foreach ($applications as $application)
                @php
                    $vacancy = $application->jobVacancy;
                    $isLive = $vacancy->status === VacancyStatus::Active && ! $vacancy->isExpired();

                    $vacancyNotice = match (true) {
                        $vacancy->status === VacancyStatus::TakenDown => 'Lowongan ini sudah diturunkan admin setelah laporan pengguna terbukti.',
                        $vacancy->status === VacancyStatus::Closed => 'Perusahaan sudah menutup lowongan ini.',
                        $vacancy->status === VacancyStatus::Expired || $vacancy->isExpired() => 'Masa tayang lowongan ini sudah berakhir.',
                        default => null,
                    };
                @endphp

                <article class="card p-4 sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-slate-900">
                                @if ($isLive)
                                    <a href="{{ route('vacancies.show', $vacancy) }}"
                                       class="hover:text-brand-700">{{ $vacancy->title }}</a>
                                @else
                                    {{ $vacancy->title }}
                                @endif
                            </h2>
                            <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-600">
                                <span>{{ $vacancy->company->name }}</span>
                                @if ($vacancy->company->isVerified())
                                    <x-badge classes="bg-brand-100 text-brand-700">&check; Terverifikasi</x-badge>
                                @endif
                            </p>
                        </div>
                        <x-badge :status="$application->status" />
                    </div>

                    <dl class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
                        <div>
                            <dt class="sr-only">Lokasi</dt>
                            <dd>{{ $vacancy->is_remote ? 'Remote' : $vacancy->location }}</dd>
                        </div>
                        <div>
                            <dt class="sr-only">Jenis pekerjaan</dt>
                            <dd>{{ $vacancy->employment_type->label() }}</dd>
                        </div>
                        <div>
                            <dt class="sr-only">Bidang</dt>
                            <dd>{{ $vacancy->category->name }}</dd>
                        </div>
                        @if ($vacancy->salary_min)
                            <div>
                                <dt class="sr-only">Gaji</dt>
                                <dd class="font-medium text-slate-700">
                                    Rp{{ number_format($vacancy->salary_min, 0, ',', '.') }}
                                    @if ($vacancy->salary_max)
                                        &ndash; Rp{{ number_format($vacancy->salary_max, 0, ',', '.') }}
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>

                    <x-application-progress :status="$application->status" class="mt-4" />

                    <p class="mt-3 text-xs text-slate-500">
                        Dikirim {{ $application->created_at->translatedFormat('d F Y') }}
                        @if ($application->status_updated_at)
                            &middot; Diperbarui {{ $application->status_updated_at->diffForHumans() }}
                        @elseif ($application->status === ApplicationStatus::New)
                            &middot; menunggu {{ $application->created_at->diffForHumans(syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE) }}
                        @endif
                    </p>

                    @if ($vacancyNotice)
                        <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-900">{{ $vacancyNotice }}</p>
                    @endif

                    @if (filled($application->status_note))
                        <div class="mt-3 rounded-lg border border-brand-100 bg-brand-50 px-3 py-2.5">
                            <p class="text-xs font-semibold text-brand-800">
                                Catatan dari {{ $vacancy->company->name }}
                            </p>
                            <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ $application->status_note }}</p>
                        </div>
                    @endif

                    @if ($application->status === ApplicationStatus::Accepted)
                        {{--
                            Kontak PIC hanya dibuka setelah pelamar diterima, karena KalaLuang
                            tidak punya fitur percakapan: tindak lanjut terjadi di luar platform.
                        --}}
                        <div class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                            <p class="text-sm font-semibold text-emerald-900">Lamaran Anda diterima &mdash; ini cara menghubungi mereka</p>

                            <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs text-emerald-800">Narahubung</dt>
                                    <dd class="font-medium text-slate-800">{{ $vacancy->company->user->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-emerald-800">Email</dt>
                                    <dd>
                                        <a href="mailto:{{ $vacancy->company->user->email }}"
                                           class="font-medium text-brand-700 hover:underline">
                                            {{ $vacancy->company->user->email }}
                                        </a>
                                    </dd>
                                </div>
                                @if ($vacancy->company->address)
                                    <div class="sm:col-span-2">
                                        <dt class="text-xs text-emerald-800">Alamat</dt>
                                        <dd class="text-slate-800">{{ $vacancy->company->address }}</dd>
                                    </div>
                                @endif
                                @if ($vacancy->company->website)
                                    <div class="sm:col-span-2">
                                        <dt class="text-xs text-emerald-800">Situs</dt>
                                        <dd>
                                            <a href="{{ $vacancy->company->website }}" target="_blank"
                                               rel="noopener nofollow" class="text-brand-700 hover:underline">
                                                {{ $vacancy->company->website }}
                                            </a>
                                        </dd>
                                    </div>
                                @endif
                            </dl>

                            <p class="mt-2.5 text-xs text-emerald-800">
                                Perusahaan juga sudah menerima email dan nomor HP Anda. Ingat: perusahaan di KalaLuang
                                tidak pernah meminta biaya dalam tahap apa pun.
                            </p>
                        </div>
                    @endif

                    @if ($application->status === ApplicationStatus::Rejected)
                        <a href="{{ route('vacancies.index', ['category' => $vacancy->category->slug]) }}"
                           class="mt-3 inline-block text-sm font-semibold text-brand-700 hover:underline">
                            Cari lowongan {{ $vacancy->category->name }} lainnya
                        </a>
                    @endif

                    @if (filled($application->cover_letter))
                        <details class="group mt-3 border-t border-slate-100 pt-3">
                            <summary class="cursor-pointer list-none text-sm font-semibold text-brand-700">
                                <span class="group-open:hidden">Lihat surat lamaran yang dikirim</span>
                                <span class="hidden group-open:inline">Sembunyikan surat lamaran</span>
                            </summary>
                            <p class="mt-2 text-sm whitespace-pre-line text-slate-700">{{ $application->cover_letter }}</p>
                        </details>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $applications->links() }}</div>
    @endif
</x-layout>
