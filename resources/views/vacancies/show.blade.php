<x-layout :title="$vacancy->title">
    <nav class="mb-4 text-sm text-slate-500">
        <a href="{{ route('vacancies.index') }}" class="hover:text-brand-700">&larr; Kembali ke katalog</a>
    </nav>

    <article class="card p-5 sm:p-7">
        <div class="flex items-start gap-4">
            @if ($vacancy->company->logo_path)
                <img src="{{ Storage::url($vacancy->company->logo_path) }}" alt=""
                     class="size-14 shrink-0 rounded-xl object-cover">
            @endif
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">{{ $vacancy->title }}</h1>
                <p class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                    <span class="font-medium">{{ $vacancy->company->name }}</span>
                    @if ($vacancy->company->isVerified())
                        <x-badge classes="bg-brand-100 text-brand-700">&check; Terverifikasi</x-badge>
                    @endif
                </p>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap gap-2">
            <x-badge classes="bg-emerald-100 text-emerald-800">Tanpa pengalaman</x-badge>
            <x-badge>{{ $vacancy->employment_type->label() }}</x-badge>
            <x-badge>{{ $vacancy->is_remote ? 'Remote' : $vacancy->location }}</x-badge>
            <x-badge>{{ $vacancy->category->name }}</x-badge>
            <x-badge>Min. {{ $vacancy->min_education->label() }}</x-badge>
        </div>

        <dl class="mt-5 grid gap-4 border-y border-slate-200 py-5 sm:grid-cols-3">
            <div>
                <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Rentang gaji</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">
                    @if ($vacancy->salary_min)
                        Rp{{ number_format($vacancy->salary_min, 0, ',', '.') }}
                        @if ($vacancy->salary_max)
                            – Rp{{ number_format($vacancy->salary_max, 0, ',', '.') }}
                        @endif
                    @else
                        Tidak dicantumkan
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Ditayangkan</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">
                    {{ $vacancy->published_at?->translatedFormat('d F Y') ?? '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Berlaku hingga</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">
                    {{ $vacancy->expires_at?->translatedFormat('d F Y') ?? '—' }}
                </dd>
            </div>
        </dl>

        <section class="mt-6">
            <h2 class="font-bold text-slate-900">Deskripsi pekerjaan</h2>
            <p class="mt-2 text-sm whitespace-pre-line text-slate-700">{{ $vacancy->description }}</p>
        </section>

        <section class="mt-6">
            <h2 class="font-bold text-slate-900">Persyaratan</h2>
            <ul class="mt-2 space-y-1.5 text-sm text-slate-700">
                @foreach (preg_split('/\r\n|\r|\n/', $vacancy->requirements, flags: PREG_SPLIT_NO_EMPTY) as $requirement)
                    <li class="flex gap-2">
                        <span class="mt-2 size-1.5 shrink-0 rounded-full bg-brand-600"></span>
                        {{ trim($requirement) }}
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="mt-6">
            <h2 class="font-bold text-slate-900">Cara melamar</h2>
            <p class="mt-2 text-sm whitespace-pre-line text-slate-700">{{ $vacancy->how_to_apply }}</p>
        </section>

        @if ($vacancy->company->description)
            <section class="mt-6 rounded-lg bg-slate-50 p-4">
                <h2 class="font-bold text-slate-900">Tentang {{ $vacancy->company->name }}</h2>
                <p class="mt-2 text-sm text-slate-700">{{ $vacancy->company->description }}</p>
                <p class="mt-2 text-sm text-slate-600">
                    {{ $vacancy->company->industry }} · {{ $vacancy->company->city }}
                    @if ($vacancy->company->website)
                        · <a href="{{ $vacancy->company->website }}" rel="nofollow noopener" target="_blank"
                             class="font-medium text-brand-700 hover:underline">Situs resmi</a>
                    @endif
                </p>
            </section>
        @endif

        <div class="mt-7 border-t border-slate-200 pt-6">
            @guest
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('login') }}" class="btn-primary">Masuk untuk Melamar</a>
                    <a href="{{ route('register') }}" class="btn-secondary">Daftar sebagai Pencari Kerja</a>
                </div>
            @endguest

            @auth
                @if (auth()->user()->isJobSeeker())
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        @if ($hasApplied)
                            <span class="btn-secondary pointer-events-none">Anda sudah melamar lowongan ini</span>
                            <a href="{{ route('job-seeker.applications.index') }}"
                               class="text-sm font-semibold text-brand-700 hover:underline">Lihat status lamaran</a>
                        @else
                            <form method="POST" action="{{ route('job-seeker.applications.store', $vacancy) }}"
                                  class="flex-1 space-y-3">
                                @csrf
                                <div>
                                    <label for="cover_letter" class="label">Surat lamaran (opsional)</label>
                                    <textarea id="cover_letter" name="cover_letter" rows="3" class="input"
                                              placeholder="Ceritakan singkat mengapa Anda tertarik.">{{ old('cover_letter') }}</textarea>
                                </div>
                                <button type="submit" class="btn-primary w-full sm:w-auto">Lamar Sekarang</button>
                            </form>
                        @endif
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <form method="POST"
                              action="{{ $hasSaved ? route('job-seeker.saved.destroy', $vacancy) : route('job-seeker.saved.store', $vacancy) }}">
                            @csrf
                            @if ($hasSaved)
                                @method('DELETE')
                            @endif
                            <button type="submit" class="btn-secondary">
                                {{ $hasSaved ? 'Hapus dari tersimpan' : 'Simpan lowongan' }}
                            </button>
                        </form>
                    </div>
                @endif

                @if ($canReport)
                    <details class="mt-5 rounded-lg border border-slate-200 p-4">
                        <summary class="cursor-pointer text-sm font-semibold text-slate-700">
                            Laporkan lowongan ini
                        </summary>
                        <form method="POST" action="{{ route('vacancies.report', $vacancy) }}" class="mt-4 space-y-3">
                            @csrf
                            <div>
                                <label for="reason" class="label">Alasan</label>
                                <select id="reason" name="reason" class="input" required>
                                    @foreach (App\Enums\ReportReason::cases() as $reason)
                                        <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="description" class="label">Keterangan (opsional)</label>
                                <textarea id="description" name="description" rows="3" class="input"
                                          placeholder="Jelaskan apa yang mencurigakan."></textarea>
                            </div>
                            <button type="submit" class="btn-danger">Kirim Laporan</button>
                        </form>
                    </details>
                @endif
            @endauth
        </div>
    </article>
</x-layout>
