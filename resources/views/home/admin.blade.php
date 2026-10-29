@php
    $queueTotal = $pendingCompanies + $waitingPayments + $vacanciesUnderReview + $pendingReports;
@endphp

<x-layout wide>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Halo, {{ auth()->user()->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">
                @if ($queueTotal > 0)
                    Ada {{ $queueTotal }} pekerjaan yang menunggu tindakan Anda.
                @else
                    Semua antrean kosong. Tidak ada yang menunggu tindakan.
                @endif
            </p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-primary">Buka Panel Admin</a>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-stat label="Verifikasi perusahaan" :value="$pendingCompanies" hint="Menunggu pemeriksaan dokumen"
                :href="route('admin.companies.index')" />
        <x-stat label="Konfirmasi pembayaran" :value="$waitingPayments" hint="Bukti bayar masuk"
                :href="route('admin.payments.index')" />
        <x-stat label="Kurasi lowongan" :value="$vacanciesUnderReview" hint="Sedang ditinjau"
                :href="route('admin.curation.index')" />
        <x-stat label="Laporan pengguna" :value="$pendingReports" hint="Belum ditinjau"
                :href="route('admin.reports.index')" />
    </div>

    <div class="card mt-6 p-5">
        <h2 class="font-bold text-slate-900">Pengingat saringan tiga lapis</h2>
        <ul class="mt-3 space-y-2 text-sm text-slate-600">
            <li><strong class="text-slate-900">Lapis 1</strong> — hanya verifikasi perusahaan dengan NIB yang sah.</li>
            <li><strong class="text-slate-900">Lapis 2</strong> — tolak lowongan yang mensyaratkan pengalaman atau memungut biaya dari pelamar.</li>
            <li><strong class="text-slate-900">Lapis 3</strong> — tindak laporan pengguna; lowongan terbukti bermasalah diturunkan.</li>
        </ul>
    </div>
</x-layout>
