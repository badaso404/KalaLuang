@php
    $completedSteps = count(array_filter($checklist));
    $totalSteps = count($checklist);
@endphp

<x-layout wide>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Halo, {{ auth()->user()->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">
                Ringkasan lamaran Anda dan lowongan baru yang cocok dengan profil Anda.
            </p>
        </div>
        <a href="{{ route('vacancies.index') }}" class="btn-primary">Cari Lowongan</a>
    </div>

    @unless ($isEmailVerified)
        <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-semibold">Email Anda belum diverifikasi.</p>
            <p class="mt-1">
                Verifikasi email diperlukan sebelum Anda bisa melamar atau menyimpan lowongan.
                <a href="{{ route('verification.notice') }}" class="font-semibold underline">Kirim ulang tautan verifikasi</a>.
            </p>
        </div>
    @endunless

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_20rem]">
        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <x-stat label="Total lamaran" :value="$applicationTotal"
                        :href="route('job-seeker.applications.index')" />
                <x-stat label="Menunggu ditinjau" :value="$newCount" hint="Status Baru"
                        :href="route('job-seeker.applications.index')" />
                <x-stat label="Sedang diproses" :value="$reviewedCount" hint="Status Diproses"
                        :href="route('job-seeker.applications.index')" />
                <x-stat label="Lowongan tersimpan" :value="$savedCount"
                        :href="route('job-seeker.saved.index')" />
            </div>

            @if ($acceptedCount > 0)
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    Selamat! {{ $acceptedCount }} lamaran Anda sudah berstatus <strong>Diterima</strong>.
                    <a href="{{ route('job-seeker.applications.index') }}" class="font-semibold underline">Lihat detailnya</a>.
                </div>
            @endif

            <section class="card p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-bold text-slate-900">Lamaran terakhir</h2>
                    <a href="{{ route('job-seeker.applications.index') }}"
                       class="text-sm font-semibold text-brand-700 hover:underline">Lihat semua</a>
                </div>

                @if ($latestApplications->isEmpty())
                    <p class="mt-3 text-sm text-slate-600">
                        Anda belum mengirim lamaran. Mulai dari
                        <a href="{{ route('vacancies.index') }}" class="font-semibold text-brand-700 hover:underline">katalog lowongan</a>.
                    </p>
                @else
                    <ul class="mt-4 divide-y divide-slate-100">
                        @foreach ($latestApplications as $application)
                            <li class="flex flex-wrap items-center justify-between gap-2 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <a href="{{ route('vacancies.show', $application->jobVacancy) }}"
                                       class="block truncate font-semibold text-slate-900 hover:text-brand-700">
                                        {{ $application->jobVacancy->title }}
                                    </a>
                                    <p class="text-sm text-slate-600">
                                        {{ $application->jobVacancy->company->name }} &middot;
                                        dilamar {{ $application->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <x-badge :status="$application->status" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section>
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            {{ $matchedVacancies->isNotEmpty() ? 'Cocok dengan profil Anda' : 'Lowongan terbaru' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-600">
                            @if ($matchedVacancies->isNotEmpty())
                                Disaring dari jenjang pendidikan
                                @if (filled($profile?->city))
                                    dan kota domisili
                                @endif
                                yang Anda isi di profil, dan belum pernah Anda lamar.
                            @else
                                Lengkapi pendidikan dan kota domisili di profil agar kami bisa menyaring yang paling cocok.
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('vacancies.index') }}"
                       class="shrink-0 text-sm font-semibold text-brand-700 hover:underline">Lihat semua</a>
                </div>

                @php
                    $recommendations = $matchedVacancies->isNotEmpty() ? $matchedVacancies : $newestVacancies;
                @endphp

                @if ($recommendations->isEmpty())
                    <div class="mt-4">
                        <x-empty message="Belum ada lowongan baru yang bisa ditampilkan untuk Anda." />
                    </div>
                @else
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach ($recommendations as $vacancy)
                            <x-vacancy-card :$vacancy class="h-full" />
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-4 lg:sticky lg:top-20 lg:self-start">
            <section class="card p-5">
                <h2 class="font-bold text-slate-900">Kesiapan profil</h2>
                <p class="mt-1 text-sm text-slate-600">
                    {{ $completedSteps }} dari {{ $totalSteps }} bagian sudah terisi.
                </p>

                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100" role="presentation">
                    <div class="h-full rounded-full bg-brand-600 transition-all"
                         style="width: {{ round($completedSteps / $totalSteps * 100) }}%"></div>
                </div>

                <ul class="mt-4 space-y-2.5">
                    @foreach ($checklist as $step => $isDone)
                        <li class="flex items-start gap-2.5 text-sm">
                            @if ($isDone)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                     stroke-linecap="round" stroke-linejoin="round"
                                     class="mt-0.5 size-4 shrink-0 text-emerald-600" aria-hidden="true">
                                    <path d="m5 13 4 4L19 7" />
                                </svg>
                                <span class="text-slate-500 line-through">{{ $step }}</span>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     class="mt-0.5 size-4 shrink-0 text-slate-300" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" />
                                </svg>
                                <span class="font-medium text-slate-800">{{ $step }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>

                @unless ($isProfileComplete)
                    <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-900">
                        Data pendidikan dan CV wajib terisi sebelum Anda bisa melamar.
                    </p>
                @endunless

                <a href="{{ route('job-seeker.profile.edit') }}"
                   @class(['mt-4 w-full', $isProfileComplete ? 'btn-secondary' : 'btn-primary'])>
                    {{ $isProfileComplete ? 'Perbarui profil' : 'Lengkapi profil' }}
                </a>
            </section>

            <section class="card p-5">
                <h2 class="font-bold text-slate-900">Ingat selalu</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>Perusahaan di KalaLuang tidak pernah meminta biaya dari pelamar.</li>
                    <li>Menemukan yang mencurigakan? Gunakan tombol <strong>Laporkan lowongan</strong> di detail lowongan.</li>
                </ul>
            </section>
        </aside>
    </div>
</x-layout>
