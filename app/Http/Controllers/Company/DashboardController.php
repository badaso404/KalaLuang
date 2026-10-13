<?php

namespace App\Http\Controllers\Company;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Enums\VacancyStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $company = $request->user()->company;

        if ($company === null) {
            return redirect()->route('company.profile.edit')
                ->with('status', 'Lengkapi profil perusahaan Anda untuk mulai menggunakan dasbor.');
        }

        $vacancyCounts = $company->jobVacancies()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('company.dashboard', [
            'company' => $company,
            'activeCount' => (int) ($vacancyCounts[VacancyStatus::Active->value] ?? 0),
            'underReviewCount' => (int) ($vacancyCounts[VacancyStatus::UnderReview->value] ?? 0),
            'pendingPaymentCount' => (int) ($vacancyCounts[VacancyStatus::PendingPayment->value] ?? 0),
            'expiredCount' => (int) ($vacancyCounts[VacancyStatus::Expired->value] ?? 0),
            'applicantCount' => $this->applications($company->id)->count(),
            'newApplicantCount' => $this->applications($company->id)
                ->where('status', ApplicationStatus::New)
                ->count(),
            'unpaidPayments' => $company->payments()
                ->with('jobVacancy')
                ->whereIn('status', [PaymentStatus::Unpaid, PaymentStatus::Rejected])
                ->latest()
                ->get(),
            'latestApplications' => $this->applications($company->id)
                ->with(['user', 'jobVacancy'])
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    /**
     * @return Builder<Application>
     */
    protected function applications(int $companyId): Builder
    {
        return Application::query()->whereHas(
            'jobVacancy',
            fn (Builder $vacancy) => $vacancy->where('company_id', $companyId),
        );
    }
}
