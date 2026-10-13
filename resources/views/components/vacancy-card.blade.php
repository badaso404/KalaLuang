@props(['vacancy'])

<article class="card group relative p-4 transition hover:border-brand-200 hover:shadow-md focus-within:ring-2 focus-within:ring-brand-200 sm:p-5">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h3 class="truncate text-base font-semibold text-slate-900 group-hover:text-brand-700 sm:text-lg">
                {{-- Overlay transparan agar seluruh kartu dapat diklik, bukan hanya judulnya. --}}
                <a href="{{ route('vacancies.show', $vacancy) }}"
                   class="after:absolute after:inset-0 after:content-[''] focus:outline-none">{{ $vacancy->title }}</a>
            </h3>
            <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-600">
                <span class="font-medium">{{ $vacancy->company->name }}</span>
                @if ($vacancy->company->isVerified())
                    <x-badge classes="bg-brand-100 text-brand-700">&check; Terverifikasi</x-badge>
                @endif
            </p>
        </div>
        @if ($vacancy->company->logo_path)
            <img src="{{ Storage::url($vacancy->company->logo_path) }}" alt=""
                 class="size-10 shrink-0 rounded-lg object-cover">
        @endif
    </div>

    <dl class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
        <div class="flex gap-1.5">
            <dt class="sr-only">Lokasi</dt>
            <dd>{{ $vacancy->is_remote ? 'Remote' : $vacancy->location }}</dd>
        </div>
        <div class="flex gap-1.5">
            <dt class="sr-only">Jenis pekerjaan</dt>
            <dd>{{ $vacancy->employment_type->label() }}</dd>
        </div>
        <div class="flex gap-1.5">
            <dt class="sr-only">Bidang</dt>
            <dd>{{ $vacancy->category->name }}</dd>
        </div>
    </dl>

    <div class="mt-3 flex flex-wrap items-center gap-2">
        <x-badge classes="bg-emerald-100 text-emerald-800">Tanpa pengalaman</x-badge>
        <x-badge>Min. {{ $vacancy->min_education->label() }}</x-badge>
        @if ($vacancy->salary_min)
            <span class="text-sm font-medium text-slate-700">
                Rp{{ number_format($vacancy->salary_min, 0, ',', '.') }}
                @if ($vacancy->salary_max)
                    – Rp{{ number_format($vacancy->salary_max, 0, ',', '.') }}
                @endif
            </span>
        @endif
    </div>

    @if ($vacancy->published_at)
        <p class="mt-3 text-xs text-slate-500">Ditayangkan {{ $vacancy->published_at->diffForHumans() }}</p>
    @endif
</article>
