<x-admin-panel :title="'Verifikasi: '.$company->name">
    <nav class="mb-4 text-sm text-slate-500">
        <a href="{{ route('admin.companies.index') }}" class="hover:text-brand-700">&larr; Kembali ke daftar perusahaan</a>
    </nav>

    <div class="card p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-slate-900">{{ $company->name }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ $company->industry }} · {{ $company->city }}</p>
            </div>
            <x-badge :status="$company->verification_status" />
        </div>

        <dl class="mt-5 grid gap-4 border-y border-slate-200 py-5 sm:grid-cols-2">
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Penanggung jawab</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $company->user->name }}</dd>
                <dd class="text-sm text-slate-600">{{ $company->user->email }}</dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Situs / medsos</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-900">
                    @if ($company->website)
                        <a href="{{ $company->website }}" target="_blank" rel="noopener nofollow"
                           class="text-brand-700 hover:underline">{{ $company->website }}</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Alamat</dt>
                <dd class="mt-1 text-sm text-slate-700">{{ $company->address ?? '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Deskripsi</dt>
                <dd class="mt-1 text-sm text-slate-700">{{ $company->description ?? '—' }}</dd>
            </div>
        </dl>

        @if ($company->verification_note)
            <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                <p class="font-semibold">Catatan terakhir</p>
                <p class="mt-1">{{ $company->verification_note }}</p>
                @if ($company->verifier)
                    <p class="mt-1 text-xs text-slate-500">oleh {{ $company->verifier->name }}</p>
                @endif
            </div>
        @endif

        <section class="mt-5">
            <h2 class="font-bold text-slate-900">Dokumen legalitas</h2>
            @if ($company->documents->isEmpty())
                <p class="mt-2 text-sm text-rose-700">
                    Perusahaan belum mengunggah dokumen apa pun. NIB wajib sebelum dapat diverifikasi.
                </p>
            @else
                <ul class="mt-3 divide-y divide-slate-100">
                    @foreach ($company->documents as $document)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $document->type->label() }}</p>
                                <p class="text-xs text-slate-500">
                                    Diunggah {{ $document->uploaded_at->translatedFormat('d F Y') }}
                                </p>
                            </div>
                            <a href="{{ route('company-documents.show', $document) }}" target="_blank"
                               class="btn-secondary">Buka</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-2">
        <form method="POST" action="{{ route('admin.companies.verify', $company) }}" class="card p-5">
            @csrf
            <h2 class="font-bold text-slate-900">Setujui verifikasi</h2>
            <p class="mt-1 text-sm text-slate-600">
                Perusahaan mendapat lencana "Terverifikasi" dan dapat memasang lowongan.
            </p>
            <button type="submit" class="btn-primary mt-4">Tandai Terverifikasi</button>
        </form>

        <form method="POST" action="{{ route('admin.companies.reject', $company) }}" class="card p-5">
            @csrf
            <h2 class="font-bold text-slate-900">Tolak verifikasi</h2>
            <label for="verification_note" class="label mt-3">Alasan penolakan</label>
            <textarea id="verification_note" name="verification_note" rows="3" class="input" required
                      placeholder="mis. NIB tidak terbaca, mohon unggah ulang."></textarea>
            <button type="submit" class="btn-secondary mt-4">Tolak & Kirim Catatan</button>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.companies.suspend', $company) }}" class="card mt-5 p-5">
        @csrf
        <h2 class="font-bold text-rose-800">Tangguhkan perusahaan</h2>
        <p class="mt-1 text-sm text-slate-600">
            Untuk pelanggaran berat. Seluruh lowongan yang sedang tayang akan langsung diturunkan.
        </p>
        <label for="suspend_note" class="label mt-3">Alasan penangguhan</label>
        <textarea id="suspend_note" name="verification_note" rows="3" class="input" required
                  placeholder="mis. Terbukti meminta biaya dari pelamar."></textarea>
        <button type="submit" class="btn-danger mt-4">Tangguhkan Perusahaan</button>
    </form>
</x-admin-panel>
