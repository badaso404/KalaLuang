<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * An invoice and its transfer receipt belong to the paying company; admins
     * read them to confirm or reject the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->company !== null
            && $user->company->id === $payment->company_id;
    }
}
