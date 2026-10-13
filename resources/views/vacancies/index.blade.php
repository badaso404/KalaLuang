<x-layout title="Katalog Lowongan" wide>
    <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Katalog Lowongan</h1>
    <p class="mt-1 text-sm text-slate-600">
        Semua lowongan di bawah berasal dari perusahaan terverifikasi dan menerima lulusan baru tanpa pengalaman.
    </p>

    <div class="mt-6 grid gap-6 lg:grid-cols-[16rem_1fr]">
        <form method="GET" action="{{ route('vacancies.index') }}" class="card h-fit p-4">
            <h2 class="font-semibold text-slate-900">Filter</h2>

            <div class="mt-4">
                <label for="q" class="label">Kata kunci</label>
                <input id="q" name="q" type="search" class="input"
                       value="{{ $filters['q'] ?? '' }}" placeholder="Posisi atau perusahaan">
            </div>

            <fieldset class="mt-4">
                <legend class="label">Jenis pekerjaan</legend>
                <div class="space-y-1.5">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="radio" name="employment_type" value=""
                               @checked(empty($filters['employment_type']))
                               class="size-4 border-slate-300 text-brand-600 focus:ring-brand-200">
                        Semua
                    </label>
                    @foreach ($employmentTypes as $type)
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="radio" name="employment_type" value="{{ $type->value }}"
                                   @checked(($filters['employment_type'] ?? null) === $type->value)
                                   class="size-4 border-slate-300 text-brand-600 focus:ring-brand-200">
                            {{ $type->label() }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="mt-4">
                <label for="category" class="label">Bidang</label>
                <select id="category" name="category" class="input">
                    <option value="">Semua bidang</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(($filters['category'] ?? null) === $category->slug)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mt-4">
                <label for="location" class="label">Lokasi</label>
                <select id="location" name="location" class="input">
                    <option value="">Semua lokasi</option>
                    <option value="remote" @selected(($filters['location'] ?? null) === 'remote')>Remote</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location }}" @selected(($filters['location'] ?? null) === $location)>
                            {{ $location }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mt-5 flex gap-2">
                <button type="submit" class="btn-primary flex-1">Terapkan</button>
                <a href="{{ route('vacancies.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>

        <div>
            <p class="text-sm text-slate-600">{{ $vacancies->total() }} lowongan ditemukan</p>

            @if ($vacancies->isEmpty())
                <div class="mt-4">
                    <x-empty message="Tidak ada lowongan yang cocok dengan filter Anda.">
                        <a href="{{ route('vacancies.index') }}" class="btn-secondary">Hapus filter</a>
                    </x-empty>
                </div>
            @else
                <div class="mt-4 space-y-4">
                    @foreach ($vacancies as $vacancy)
                        <x-vacancy-card :$vacancy />
                    @endforeach
                </div>

                <div class="mt-6">{{ $vacancies->links() }}</div>
            @endif
        </div>
    </div>
</x-layout>
