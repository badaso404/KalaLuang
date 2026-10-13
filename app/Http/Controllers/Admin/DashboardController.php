<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CompanyVerificationStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\VacancyStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\Payment;
use App\Models\User;
use App\Models\VacancyReport;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'pendingCompanies' => Company::query()
                ->where('verification_status', CompanyVerificationStatus::Pending)
                ->count(),
            'waitingPayments' => Payment::query()
                ->where('status', PaymentStatus::WaitingConfirmation)
                ->count(),
            'vacanciesUnderReview' => JobVacancy::query()
                ->where('status', VacancyStatus::UnderReview)
                ->count(),
            'pendingReports' => VacancyReport::query()
                ->where('status', ReportStatus::Pending)
                ->count(),
            'activeVacancies' => JobVacancy::query()->published()->count(),
            'verifiedCompanies' => Company::query()->verified()->count(),
            'jobSeekers' => User::query()->where('role', UserRole::JobSeeker)->count(),
            'confirmedRevenue' => (int) Payment::query()
                ->where('status', PaymentStatus::Paid)
                ->sum('amount'),
        ]);
    }
}
