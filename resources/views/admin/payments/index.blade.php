<x-admin-panel title="Konfirmasi Pembayaran" heading="Konfirmasi Pembayaran">
    <x-status-filter :action="route('admin.payments.index')" :statuses="$statuses"
                     :selected="$filters['status'] ?? null" all-label="Semua pembayaran" />

    @if ($payments->isEmpty())
        <x-empty message="Tidak ada pembayaran dengan status ini." />
    @else
        <div class="space-y-3">
            @foreach ($payments as $payment)
                <article class="card p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-slate-900">{{ $payment->invoice_number }}</h2>
                            <p class="mt-0.5 text-sm text-slate-600">
                                {{ $payment->company->name }} — {{ $payment->jobVacancy->title }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Rp{{ number_format($payment->amount, 0, ',', '.') }} ·
                                dibuat {{ $payment->created_at->translatedFormat('d F Y') }}
                            </p>
                        </div>
                        <x-badge :status="$payment->status" />
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @if ($payment->proof_path)
                            <a href="{{ Storage::url($payment->proof_path) }}" target="_blank" class="btn-secondary">
                                Lihat Bukti Bayar
                            </a>
                        @else
                            <span class="text-sm text-slate-500">Belum ada bukti bayar.</span>
                        @endif

                        @if ($payment->status === App\Enums\PaymentStatus::WaitingConfirmation)
                            <form method="POST" action="{{ route('admin.payments.confirm', $payment) }}">
                                @csrf
                                <button type="submit" class="btn-primary">Konfirmasi Lunas</button>
                            </form>
                            <form method="POST" action="{{ route('admin.payments.reject', $payment) }}">
                                @csrf
                                <button type="submit" class="btn-danger">Tolak</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $payments->links() }}</div>
    @endif
</x-admin-panel>
