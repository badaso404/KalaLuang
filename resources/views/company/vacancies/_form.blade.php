@php
    $vacancy ??= null;
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="title" class="label">Judul lowongan</label>
        <input id="title" name="title" type="text" class="input" required
               value="{{ old('title', $vacancy?->title) }}" placeholder="mis. Staf Administrasi Junior">
    </div>

    <div>
        <label for="category_id" class="label">Bidang</label>
        <select id="category_id" name="category_id" class="input" required>
            <option value="">Pilih bidang</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                    @selected((int) old('category_id', $vacancy?->category_id) === $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="employment_type" class="label">Jenis pekerjaan</label>
        <select id="employment_type" name="employment_type" class="input" required>
            @foreach ($employmentTypes as $type)
                <option value="{{ $type->value }}"
                    @selected(old('employment_type', $vacancy?->employment_type?->value) === $type->value)>
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="location" class="label">Lokasi</label>
        <input id="location" name="location" type="text" class="input"
               value="{{ old('location', $vacancy?->location) }}" placeholder="mis. Jakarta">
        <label class="mt-2 flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_remote" value="1"
                   @checked(old('is_remote', $vacancy?->is_remote))
                   class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
            Lowongan ini remote (lokasi tidak wajib)
        </label>
    </div>

    <div>
        <label for="min_education" class="label">Pendidikan minimal</label>
        <select id="min_education" name="min_education" class="input" required>
            @foreach ($educationLevels as $level)
                <option value="{{ $level->value }}"
                    @selected(old('min_education', $vacancy?->min_education?->value) === $level->value)>
                    {{ $level->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="salary_min" class="label">Gaji minimal (opsional)</label>
        <input id="salary_min" name="salary_min" type="number" min="0" class="input"
               value="{{ old('salary_min', $vacancy?->salary_min) }}" placeholder="4000000">
    </div>

    <div>
        <label for="salary_max" class="label">Gaji maksimal (opsional)</label>
        <input id="salary_max" name="salary_max" type="number" min="0" class="input"
               value="{{ old('salary_max', $vacancy?->salary_max) }}" placeholder="5000000">
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="label">Deskripsi pekerjaan</label>
        <textarea id="description" name="description" rows="5" class="input" required
                  placeholder="Jelaskan tanggung jawab utama posisi ini.">{{ old('description', $vacancy?->description) }}</textarea>
    </div>

    <div class="sm:col-span-2">
        <label for="requirements" class="label">Persyaratan</label>
        <textarea id="requirements" name="requirements" rows="5" class="input" required
                  placeholder="Satu persyaratan per baris.">{{ old('requirements', $vacancy?->requirements) }}</textarea>
        <p class="mt-1 text-xs text-slate-500">Tulis satu persyaratan per baris. Jangan mensyaratkan pengalaman kerja.</p>
    </div>

    <div class="sm:col-span-2">
        <label for="how_to_apply" class="label">Cara melamar</label>
        <textarea id="how_to_apply" name="how_to_apply" rows="3" class="input" required
                  placeholder="mis. Klik Lamar Sekarang dan pastikan CV sudah terunggah.">{{ old('how_to_apply', $vacancy?->how_to_apply) }}</textarea>
    </div>
</div>

<label class="mt-5 flex items-start gap-3 rounded-lg border border-slate-300 p-4 text-sm text-slate-700 has-checked:border-brand-600 has-checked:bg-brand-50">
    <input type="checkbox" name="entry_level_declaration" value="1" required
           @checked(old('entry_level_declaration', $vacancy !== null))
           class="mt-0.5 size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
    <span>
        <span class="block font-semibold text-slate-900">Pernyataan wajib</span>
        Lowongan ini menerima lulusan baru tanpa pengalaman kerja, dan kami tidak meminta biaya apa pun dari pelamar.
    </span>
</label>
