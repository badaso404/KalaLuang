<x-layout title="Lowongan Tersimpan">
    <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Lowongan Tersimpan</h1>

    @if ($saved->isEmpty())
        <div class="mt-6">
            <x-empty message="Belum ada lowongan yang Anda simpan.">
                <a href="{{ route('vacancies.index') }}" class="btn-primary">Cari Lowongan</a>
            </x-empty>
        </div>
    @else
        <div class="mt-6 space-y-4">
            @foreach ($saved as $item)
                <div>
                    <x-vacancy-card :vacancy="$item->jobVacancy" />
                    <form method="POST" action="{{ route('job-seeker.saved.destroy', $item->jobVacancy) }}"
                          class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm font-semibold text-rose-700 hover:underline">
                            Hapus dari tersimpan
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $saved->links() }}</div>
    @endif
</x-layout>
