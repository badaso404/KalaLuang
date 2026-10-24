<x-company-panel title="Status Verifikasi" heading="Status Verifikasi">
    <section class="card p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="font-semibold text-slate-900">{{ $company->name }}</p>
                <p class="text-sm text-slate-600">
                    @if ($company->verified_at)
                        Diverifikasi {{ $company->verified_at->translatedFormat('d F Y') }}
                    @else
                        Belum diverifikasi
                    @endif
                </p>
            </div>
            <x-badge :status="$company->verification_status" />
        </div>

        @if ($company->verification_note)
            <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-semibold">Catatan admin</p>
                <p class="mt-1">{{ $company->verification_note }}</p>
            </div>
        @endif

        @if ($company->isVerified())
            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                Perusahaan Anda terverifikasi. Lencana "Terverifikasi" tampil pada setiap lowongan Anda.
                <a href="{{ route('company.vacancies.create') }}" class="font-semibold underline">Pasang lowongan</a>.
            </div>
        @elseif (! $hasNib)
            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Dokumen <strong>NIB wajib</strong> diunggah agar admin dapat memverifikasi perusahaan Anda.
            </div>
        @endif
    </section>

    <section class="card mt-5 p-5">
        <h2 class="font-bold text-slate-900">Dokumen legalitas</h2>

        @if ($company->documents->isEmpty())
            <p class="mt-3 text-sm text-slate-600">Belum ada dokumen yang diunggah.</p>
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
                           class="text-sm font-semibold text-brand-700 hover:underline">Lihat dokumen</a>
                    </li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('company.documents.store') }}" enctype="multipart/form-data"
              class="mt-5 space-y-4 border-t border-slate-200 pt-5">
            @csrf

            <div>
                <label for="type" class="label">Jenis dokumen</label>
                <select id="type" name="type" class="input" required>
                    @foreach ($documentTypes as $type)
                        <option value="{{ $type->value }}">
                            {{ $type->label() }}{{ $type === App\Enums\DocumentType::Nib ? ' (wajib)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="document" class="label">Berkas</label>
                <input id="document" name="document" type="file" required accept=".pdf,image/*"
                       class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                <p class="mt-1 text-xs text-slate-500">PDF atau gambar, maksimal 4 MB. Mengunggah ulang akan mengganti dokumen dengan jenis yang sama.</p>
            </div>

            <button type="submit" class="btn-primary">Unggah Dokumen</button>
        </form>
    </section>
</x-company-panel>
