<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentProofDownloadController extends Controller
{
    /**
     * Serve a transfer receipt to the company that paid or to an admin
     * confirming the payment.
     */
    public function __invoke(Payment $payment): StreamedResponse
    {
        $this->authorize('view', $payment);

        abort_if(
            $payment->proof_path === null || ! Storage::disk('local')->exists($payment->proof_path),
            404,
        );

        return Storage::disk('local')->response($payment->proof_path, null, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
