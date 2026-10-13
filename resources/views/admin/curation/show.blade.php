<x-admin-panel :title="'Kurasi: '.$vacancy->title">
    <nav class="mb-4 text-sm text-slate-500">
        <a href="{{ route('admin.curation.index') }}" class="hover:text-brand-700">&larr; Kembali ke daftar kurasi</a>
    </nav>

    <div class="card p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-slate-900">{{ $vacancy->title }}</h1>
                <p class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                    {{ $vacancy->company->name }}
                    <x-badge :status="$vacancy->company->verification_status" />
                </p>
            </div>
            <x-badge :status="$vacancy->status" />
        </div>

        <dl class="mt-5 grid gap-4 border-y border-slate-200 py-5 sm:grid-cols-3">
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Bidang</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $vacancy->category->name }}</dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Jenis / lokasi</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">
                    {{ $vacancy->employment_type->label() }} ·
                    {{ $vacancy->is_remote ? 'Remote' : $vacancy->location }}
                </dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Pendidikan minimal</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $vacancy->min_education->label() }}</dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Rentang gaji</dt>
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
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Entry-level</dt>
                <dd class="mt-1">
                    @if ($vacancy->requires_experience)
                        <x-badge classes="bg-rose-100 text-rose-800">Mensyaratkan pengalaman</x-badge>
                    @else
                        <x-badge classes="bg-emerald-100 text-emerald-800">Tanpa pengalaman</x-badge>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Pembayaran</dt>
                <dd class="mt-1 flex flex-wrap gap-1">
                    @forelse ($vacancy->payments as $payment)
                        <x-badge :status="$payment->status" />
                    @empty
                        <span class="text-sm text-slate-500">—</span>
                    @endforelse
                </dd>
            </div>
        </dl>

        <div class="mt-5 space-y-5">
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
    </div>

    @if ($vacancy->status === App\Enums\VacancyStatus::UnderReview)
        <div class="card mt-5 p-5">
            <h2 class="font-bold text-slate-900">Daftar periksa kurasi</h2>
            <ul class="mt-2 space-y-1 text-sm text-slate-700">
                <li>&bull; Tidak mensyaratkan pengalaman kerja.</li>
                <li>&bull; Tidak meminta biaya apa pun dari pelamar.</li>
                <li>&bull; Deskripsi dan persyaratan jelas serta masuk akal untuk lulusan baru.</li>
            </ul>
        </div>

        <div class="mt-5 grid gap-5 lg:grid-cols-2">
            <form method="POST" action="{{ route('admin.curation.approve', $vacancy) }}" class="card p-5">
                @csrf
                <h2 class="font-bold text-slate-900">Loloskan kurasi</h2>
                <p class="mt-1 text-sm text-slate-600">
                    Lowongan langsung tayang selama {{ config('kalaluang.listing_duration_days') }} hari.
                </p>
                <button type="submit" class="btn-primary mt-4">Setujui & Tayangkan</button>
            </form>

            <form method="POST" action="{{ route('admin.curation.reject', $vacancy) }}" class="card p-5">
                @csrf
                <h2 class="font-bold text-slate-900">Tolak kurasi</h2>
                <label for="review_note" class="label mt-3">Catatan untuk perusahaan</label>
                <textarea id="review_note" name="review_note" rows="3" class="input" required
                          placeholder="mis. Persyaratan masih menyebut minimal 1 tahun pengalaman."></textarea>
                <button type="submit" class="btn-secondary mt-4">Tolak & Kirim Catatan</button>
            </form>
        </div>
    @elseif ($vacancy->review_note)
        <div class="card mt-5 p-5">
            <h2 class="font-bold text-slate-900">Catatan kurasi terakhir</h2>
            <p class="mt-1 text-sm text-slate-700">{{ $vacancy->review_note }}</p>
        </div>
    @endif
</x-admin-panel>
