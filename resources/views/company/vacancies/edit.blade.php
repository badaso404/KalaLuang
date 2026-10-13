<x-company-panel title="Ubah Lowongan" heading="Ubah Lowongan">
    @if ($vacancy->review_note)
        <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <p class="font-semibold">Catatan kurasi admin</p>
            <p class="mt-1">{{ $vacancy->review_note }}</p>
            <p class="mt-2">Perbaiki lowongan lalu ajukan ulang. Anda tidak perlu membayar lagi.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('company.vacancies.update', $vacancy) }}" class="card p-5">
        @csrf
        @method('PUT')
        @include('company.vacancies._form')

        <div class="mt-5 flex flex-wrap gap-3">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
            <a href="{{ route('company.vacancies.show', $vacancy) }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</x-company-panel>
