<x-layout title="Lamaran Saya">
    <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Riwayat Lamaran</h1>
    <p class="mt-1 text-sm text-slate-600">Pantau perkembangan setiap lamaran yang Anda kirim.</p>

    @if ($applications->isEmpty())
        <div class="mt-6">
            <x-empty message="Anda belum mengirim lamaran apa pun.">
                <a href="{{ route('vacancies.index') }}" class="btn-primary">Cari Lowongan</a>
            </x-empty>
        </div>
    @else
        <div class="mt-6 space-y-3">
            @foreach ($applications as $application)
                <article class="card p-4 sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-slate-900">
                                <a href="{{ route('vacancies.show', $application->jobVacancy) }}"
                                   class="hover:text-brand-700">{{ $application->jobVacancy->title }}</a>
                            </h2>
                            <p class="mt-0.5 text-sm text-slate-600">{{ $application->jobVacancy->company->name }}</p>
                        </div>
                        <x-badge :status="$application->status" />
                    </div>

                    <p class="mt-3 text-xs text-slate-500">
                        Dikirim {{ $application->created_at->translatedFormat('d F Y') }}
                        @if ($application->status_updated_at)
                            · Diperbarui {{ $application->status_updated_at->diffForHumans() }}
                        @endif
                    </p>
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $applications->links() }}</div>
    @endif
</x-layout>
