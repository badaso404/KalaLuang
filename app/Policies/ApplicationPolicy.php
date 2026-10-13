<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * The owning company reviews applicants; the applicant reads their own row.
     */
    public function view(User $user, Application $application): bool
    {
        if ($user->id === $application->user_id) {
            return true;
        }

        return $this->manage($user, $application);
    }

    /**
     * Only the company that posted the vacancy may move the applicant along.
     */
    public function manage(User $user, Application $application): bool
    {
        return $user->isCompany()
            && $user->company !== null
            && $user->company->id === $application->jobVacancy->company_id;
    }
}
