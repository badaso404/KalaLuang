<x-admin-panel title="Panel Admin" heading="Ringkasan">
    <h2 class="mb-3 text-sm font-semibold tracking-wide text-slate-500 uppercase">Perlu tindakan</h2>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-stat label="Verifikasi perusahaan" :value="$pendingCompanies" hint="Menunggu diperiksa"
                :href="route('admin.companies.index')" />
        <x-stat label="Konfirmasi pembayaran" :value="$waitingPayments" hint="Bukti bayar masuk"
                :href="route('admin.payments.index')" />
        <x-stat label="Kurasi lowongan" :value="$vacanciesUnderReview" hint="Siap ditinjau"
                :href="route('admin.curation.index')" />
        <x-stat label="Laporan lowongan" :value="$pendingReports" hint="Belum ditinjau"
                :href="route('admin.reports.index')" />
    </div>

    <h2 class="mt-8 mb-3 text-sm font-semibold tracking-wide text-slate-500 uppercase">Statistik platform</h2>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-stat label="Lowongan tayang" :value="$activeVacancies" />
        <x-stat label="Perusahaan terverifikasi" :value="$verifiedCompanies" />
        <x-stat label="Pencari kerja" :value="$jobSeekers" />
        <x-stat label="Pendapatan terkonfirmasi"
                :value="'Rp'.number_format($confirmedRevenue, 0, ',', '.')"
                hint="Dari pembayaran lunas" />
    </div>
</x-admin-panel>
