<?php

namespace App\Policies;

use App\Models\CompanyDocument;
use App\Models\User;

class CompanyDocumentPolicy
{
    /**
     * Legality documents are readable by the company that uploaded them and
     * by admins, who need them to decide on verification.
     */
    public function view(User $user, CompanyDocument $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->company !== null
            && $user->company->id === $document->company_id;
    }
}
