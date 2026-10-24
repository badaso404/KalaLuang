<x-layout title="Profil Saya">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Profil Pencari Kerja</h1>
        @if ($profile->isComplete())
            <x-badge classes="bg-emerald-100 text-emerald-800">Profil lengkap</x-badge>
        @else
            <x-badge classes="bg-amber-100 text-amber-800">Profil belum lengkap</x-badge>
        @endif
    </div>

    @unless ($profile->isComplete())
        <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Lengkapi jenjang pendidikan, institusi, jurusan, dan unggah CV agar Anda dapat melamar lowongan.
        </p>
    @endunless

    <form method="POST" action="{{ route('job-seeker.profile.update') }}" enctype="multipart/form-data"
          class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <section class="card p-5">
            <h2 class="font-bold text-slate-900">Data pribadi</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="phone" class="label">No. HP</label>
                    <input id="phone" name="phone" type="tel" class="input"
                           value="{{ old('phone', $profile->phone) }}" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label for="city" class="label">Kota domisili</label>
                    <input id="city" name="city" type="text" class="input" value="{{ old('city', $profile->city) }}">
                </div>
                <div class="sm:col-span-2">
                    <label for="bio" class="label">Tentang saya</label>
                    <textarea id="bio" name="bio" rows="3" class="input"
                              placeholder="Ceritakan singkat latar belakang dan minat Anda.">{{ old('bio', $profile->bio) }}</textarea>
                </div>
            </div>
        </section>

        <section class="card p-5">
            <h2 class="font-bold text-slate-900">Pendidikan <span class="text-rose-600">*</span></h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="education_level" class="label">Jenjang</label>
                    <select id="education_level" name="education_level" class="input" required>
                        <option value="">Pilih jenjang</option>
                        @foreach ($educationLevels as $level)
                            <option value="{{ $level->value }}"
                                @selected(old('education_level', $profile->education_level?->value) === $level->value)>
                                {{ $level->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="graduation_year" class="label">Tahun lulus</label>
                    <input id="graduation_year" name="graduation_year" type="number" class="input" required
                           min="2000" max="{{ date('Y') + 1 }}"
                           value="{{ old('graduation_year', $profile->graduation_year) }}">
                </div>
                <div>
                    <label for="institution" class="label">Institusi</label>
                    <input id="institution" name="institution" type="text" class="input" required
                           value="{{ old('institution', $profile->institution) }}">
                </div>
                <div>
                    <label for="major" class="label">Jurusan</label>
                    <input id="major" name="major" type="text" class="input" required
                           value="{{ old('major', $profile->major) }}">
                </div>
            </div>
        </section>

        <section class="card p-5">
            <h2 class="font-bold text-slate-900">Keahlian</h2>
            <p class="mt-1 text-sm text-slate-600">Pilih keahlian yang Anda kuasai.</p>
            <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach ($skills as $skill)
                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 transition has-checked:border-brand-600 has-checked:bg-brand-50">
                        <input type="checkbox" name="skills[]" value="{{ $skill->id }}"
                               @checked(in_array($skill->id, old('skills', $selectedSkills)))
                               class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
                        {{ $skill->name }}
                    </label>
                @endforeach
            </div>
        </section>

        <section class="card p-5">
            <h2 class="font-bold text-slate-900">Portofolio & CV</h2>
            <div class="mt-4 space-y-4">
                <div>
                    <label for="portfolio_url" class="label">Link portofolio (opsional)</label>
                    <input id="portfolio_url" name="portfolio_url" type="url" class="input"
                           value="{{ old('portfolio_url', $profile->portfolio_url) }}"
                           placeholder="https://...">
                </div>
                <div>
                    <label for="cv" class="label">CV <span class="text-rose-600">*</span></label>
                    <input id="cv" name="cv" type="file" accept="application/pdf"
                           class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                    <p class="mt-1 text-xs text-slate-500">Format PDF, maksimal 2 MB.</p>

                    @if ($profile->cv_path)
                        <p class="mt-2 text-sm text-slate-700">
                            CV tersimpan:
                            <a href="{{ route('job-seeker.cv.show') }}" target="_blank"
                               class="font-semibold text-brand-700 hover:underline">Lihat CV</a>
                        </p>
                    @endif
                </div>
            </div>
        </section>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Simpan Profil</button>
            <a href="{{ route('vacancies.index') }}" class="btn-secondary">Cari Lowongan</a>
        </div>
    </form>
</x-layout>
