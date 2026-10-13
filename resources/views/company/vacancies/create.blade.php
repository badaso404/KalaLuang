<x-company-panel title="Pasang Lowongan" heading="Pasang Lowongan">
    <p class="mb-5 rounded-lg border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-900">
        Biaya pemasangan <strong>Rp{{ number_format($listingPrice, 0, ',', '.') }}</strong> per lowongan
        untuk masa tayang <strong>{{ $listingDuration }} hari</strong>. Setelah disimpan, sistem membuat tagihan.
        Lowongan tayang setelah pembayaran dikonfirmasi dan lolos kurasi admin.
    </p>

    <form method="POST" action="{{ route('company.vacancies.store') }}" class="card p-5">
        @csrf
        @include('company.vacancies._form', ['vacancy' => null])

        <div class="mt-5 flex flex-wrap gap-3">
            <button type="submit" class="btn-primary">Simpan & Buat Tagihan</button>
            <a href="{{ route('company.vacancies.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</x-company-panel>
