<?php

namespace App\Http\Controllers\Company;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function show(Request $request, Payment $payment): View
    {
        $this->authorizeOwnership($request, $payment);

        return view('company.payments.show', [
            'payment' => $payment->load('jobVacancy'),
        ]);
    }

    /**
     * Upload the manual transfer receipt so an admin can confirm it.
     */
    public function storeProof(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorizeOwnership($request, $payment);

        abort_unless(
            in_array($payment->status, [PaymentStatus::Unpaid, PaymentStatus::Rejected], strict: true),
            403,
        );

        $request->validate([
            'proof' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ], [], ['proof' => 'bukti bayar']);

        $payment->update([
            'proof_path' => $request->file('proof')->store('payment-proofs', 'public'),
            'status' => PaymentStatus::WaitingConfirmation,
        ]);

        return back()->with('status', 'Bukti bayar terkirim. Admin akan segera memverifikasi pembayaran Anda.');
    }

    protected function authorizeOwnership(Request $request, Payment $payment): void
    {
        abort_unless($request->user()->company?->id === $payment->company_id, 403);
    }
}
