<?php

namespace App\Policies;

use App\Models\JobVacancy;
use App\Models\User;

class JobVacancyPolicy
{
    /**
     * Only the owning company may manage its own vacancy.
     */
    public function manage(User $user, JobVacancy $vacancy): bool
    {
        return $user->isCompany()
            && $user->company !== null
            && $user->company->id === $vacancy->company_id;
    }

    /**
     * Content may only change while the vacancy is not live (or already down).
     */
    public function update(User $user, JobVacancy $vacancy): bool
    {
        return $this->manage($user, $vacancy) && $vacancy->isEditable();
    }

    /**
     * A vacancy can only be reported once per user, and never by its owner (BR-08).
     */
    public function report(User $user, JobVacancy $vacancy): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->company?->id === $vacancy->company_id) {
            return false;
        }

        return ! $vacancy->reports()->where('reporter_id', $user->id)->exists();
    }
}
