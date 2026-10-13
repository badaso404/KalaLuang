<x-company-panel title="Dasbor Perusahaan" heading="Ringkasan">
    <div class="card mb-5 flex flex-wrap items-center justify-between gap-3 p-4">
        <div>
            <p class="font-semibold text-slate-900">{{ $company->name }}</p>
            <p class="text-sm text-slate-600">{{ $company->industry }} · {{ $company->city }}</p>
        </div>
        <div class="flex items-center gap-3">
            <x-badge :status="$company->verification_status" />
            <a href="{{ route('company.verification.show') }}"
               class="text-sm font-semibold text-brand-700 hover:underline">Detail</a>
        </div>
    </div>

    @unless ($company->isVerified())
        <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Perusahaan Anda belum terverifikasi, sehingga Anda belum dapat memasang lowongan.
            <a href="{{ route('company.verification.show') }}" class="font-semibold underline">Unggah dokumen legalitas</a>.
        </div>
    @endunless

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-stat label="Lowongan tayang" :value="$activeCount" :href="route('company.vacancies.index')" />
        <x-stat label="Sedang ditinjau" :value="$underReviewCount" hint="Menunggu kurasi admin" />
        <x-stat label="Menunggu bayar" :value="$pendingPaymentCount" hint="Selesaikan pembayaran" />
        <x-stat label="Kedaluwarsa" :value="$expiredCount" hint="Bisa diperpanjang" />
    </div>

    <div class="mt-3 grid grid-cols-2 gap-3">
        <x-stat label="Total pelamar" :value="$applicantCount" :href="route('company.applicants.index')" />
        <x-stat label="Pelamar baru" :value="$newApplicantCount"
                :href="route('company.applicants.index', ['status' => 'new'])" />
    </div>

    @if ($unpaidPayments->isNotEmpty())
        <section class="card mt-6 p-5">
            <h2 class="font-bold text-slate-900">Tagihan menunggu pembayaran</h2>
            <ul class="mt-3 divide-y divide-slate-100">
                @foreach ($unpaidPayments as $payment)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $payment->jobVacancy->title }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $payment->invoice_number }} ·
                                Rp{{ number_format($payment->amount, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-badge :status="$payment->status" />
                            <a href="{{ route('company.payments.show', $payment) }}" class="btn-secondary">Bayar</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="card mt-6 p-5">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-bold text-slate-900">Pelamar terbaru</h2>
            <a href="{{ route('company.applicants.index') }}"
               class="text-sm font-semibold text-brand-700 hover:underline">Lihat semua</a>
        </div>

        @if ($latestApplications->isEmpty())
            <p class="mt-3 text-sm text-slate-600">Belum ada pelamar.</p>
        @else
            <ul class="mt-3 divide-y divide-slate-100">
                @foreach ($latestApplications as $application)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $application->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $application->jobVacancy->title }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-badge :status="$application->status" />
                            <a href="{{ route('company.applicants.show', $application) }}"
                               class="text-sm font-semibold text-brand-700 hover:underline">Tinjau</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-company-panel>
