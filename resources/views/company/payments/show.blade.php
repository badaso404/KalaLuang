<x-company-panel title="Tagihan" heading="Tagihan Pemasangan Lowongan">
    <div class="card p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-sm text-slate-600">Nomor tagihan</p>
                <p class="text-lg font-bold text-slate-900">{{ $payment->invoice_number }}</p>
            </div>
            <x-badge :status="$payment->status" />
        </div>

        <dl class="mt-5 grid gap-4 border-y border-slate-200 py-5 sm:grid-cols-2">
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Lowongan</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $payment->jobVacancy->title }}</dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Jumlah</dt>
                <dd class="mt-1 text-xl font-bold text-slate-900">
                    Rp{{ number_format($payment->amount, 0, ',', '.') }}
                </dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Masa tayang</dt>
                <dd class="mt-1 font-semibold text-slate-900">
                    {{ config('kalaluang.listing_duration_days') }} hari setelah lolos kurasi
                </dd>
            </div>
            <div>
                <dt class="text-xs tracking-wide text-slate-500 uppercase">Dikonfirmasi</dt>
                <dd class="mt-1 font-semibold text-slate-900">
                    {{ $payment->paid_at?->translatedFormat('d F Y H:i') ?? '—' }}
                </dd>
            </div>
        </dl>

        @if ($payment->status === App\Enums\PaymentStatus::Paid)
            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                Pembayaran sudah dikonfirmasi admin. Lowongan Anda masuk tahap kurasi atau sudah tayang.
            </div>
        @elseif ($payment->status === App\Enums\PaymentStatus::WaitingConfirmation)
            <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Bukti bayar sudah terkirim. Admin akan memverifikasi pembayaran Anda.
            </div>
        @else
            <div class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-700">
                <p class="font-semibold text-slate-900">Instruksi pembayaran</p>
                <p class="mt-2">Transfer manual sejumlah
                    <strong>Rp{{ number_format($payment->amount, 0, ',', '.') }}</strong> ke:</p>
                <p class="mt-2 font-mono">BCA 1234567890 — a.n. KalaLuang Indonesia</p>
                <p class="mt-2">Cantumkan nomor tagihan <strong>{{ $payment->invoice_number }}</strong>
                    pada berita transfer, lalu unggah bukti bayar di bawah.</p>
            </div>

            @if ($payment->status === App\Enums\PaymentStatus::Rejected)
                <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    Bukti bayar sebelumnya ditolak admin. Silakan unggah ulang bukti yang sesuai.
                </div>
            @endif
        @endif

        @if (in_array($payment->status, [App\Enums\PaymentStatus::Unpaid, App\Enums\PaymentStatus::Rejected], true))
            <form method="POST" action="{{ route('company.payments.proof', $payment) }}"
                  enctype="multipart/form-data" class="mt-5 space-y-4 border-t border-slate-200 pt-5">
                @csrf
                <div>
                    <label for="proof" class="label">Bukti bayar</label>
                    <input id="proof" name="proof" type="file" required accept=".pdf,image/*"
                           class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                    <p class="mt-1 text-xs text-slate-500">PDF atau gambar, maksimal 4 MB.</p>
                </div>
                <button type="submit" class="btn-primary">Unggah Bukti Bayar</button>
            </form>
        @endif

        @if ($payment->proof_path)
            <p class="mt-4 text-sm text-slate-700">
                Bukti terunggah:
                <a href="{{ Storage::url($payment->proof_path) }}" target="_blank"
                   class="font-semibold text-brand-700 hover:underline">Lihat berkas</a>
            </p>
        @endif
    </div>
</x-company-panel>
