<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\VacancyStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentConfirmationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(PaymentStatus::class)],
        ]);

        return view('admin.payments.index', [
            'payments' => Payment::query()
                ->with(['company', 'jobVacancy'])
                ->when(
                    $filters['status'] ?? null,
                    fn ($query, string $status) => $query->where('status', $status),
                    fn ($query) => $query->where('status', PaymentStatus::WaitingConfirmation),
                )
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'statuses' => PaymentStatus::cases(),
            'filters' => $filters,
        ]);
    }

    /**
     * Confirm the transfer and hand the vacancy over to curation.
     */
    public function confirm(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->status === PaymentStatus::WaitingConfirmation, 403);

        DB::transaction(function () use ($payment, $request): void {
            $payment->update([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'confirmed_by' => $request->user()->id,
            ]);

            $payment->jobVacancy->update(['status' => VacancyStatus::UnderReview]);
        });

        return back()->with('status', 'Pembayaran '.$payment->invoice_number.' dikonfirmasi. Lowongan masuk tahap kurasi.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->status === PaymentStatus::WaitingConfirmation, 403);

        $payment->update([
            'status' => PaymentStatus::Rejected,
            'confirmed_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Pembayaran '.$payment->invoice_number.' ditolak. Perusahaan dapat mengunggah ulang bukti bayar.');
    }
}
