<x-company-panel title="Profil Perusahaan" heading="Profil Perusahaan">
    @unless ($company)
        <p class="mb-5 rounded-lg border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-900">
            Lengkapi profil perusahaan Anda. Setelah tersimpan, Anda dapat mengunggah dokumen legalitas
            untuk diverifikasi admin.
        </p>
    @endunless

    <form method="POST" action="{{ route('company.profile.update') }}" enctype="multipart/form-data"
          class="card space-y-4 p-5">
        @csrf
        @method('PUT')

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="label">Nama perusahaan</label>
                <input id="name" name="name" type="text" class="input" required
                       value="{{ old('name', $company?->name) }}">
            </div>
            <div>
                <label for="industry" class="label">Bidang industri</label>
                <input id="industry" name="industry" type="text" class="input" required
                       value="{{ old('industry', $company?->industry) }}">
            </div>
            <div>
                <label for="city" class="label">Kota</label>
                <input id="city" name="city" type="text" class="input" required
                       value="{{ old('city', $company?->city) }}">
            </div>
            <div class="sm:col-span-2">
                <label for="address" class="label">Alamat (opsional)</label>
                <textarea id="address" name="address" rows="2" class="input">{{ old('address', $company?->address) }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <label for="website" class="label">Situs / media sosial (opsional)</label>
                <input id="website" name="website" type="url" class="input" placeholder="https://..."
                       value="{{ old('website', $company?->website) }}">
            </div>
            <div class="sm:col-span-2">
                <label for="description" class="label">Deskripsi (opsional)</label>
                <textarea id="description" name="description" rows="4" class="input">{{ old('description', $company?->description) }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <label for="logo" class="label">Logo (opsional)</label>
                <input id="logo" name="logo" type="file" accept="image/*"
                       class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                <p class="mt-1 text-xs text-slate-500">Gambar maksimal 1 MB.</p>

                @if ($company?->logo_path)
                    <img src="{{ Storage::url($company->logo_path) }}" alt="Logo {{ $company->name }}"
                         class="mt-3 size-16 rounded-lg object-cover">
                @endif
            </div>
        </div>

        <button type="submit" class="btn-primary">Simpan Profil</button>
    </form>
</x-company-panel>
