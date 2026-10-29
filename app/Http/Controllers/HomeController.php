<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\CompanyVerificationStatus;
use App\Enums\EmploymentType;
use App\Enums\PaymentStatus;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\VacancyStatus;
use App\Models\Category;
use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\Payment;
use App\Models\User;
use App\Models\VacancyReport;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * The home page is role-aware: guests get the public landing page, while
     * signed-in users get a shortcut feed for their own role.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return match (true) {
            $user === null => $this->publicLanding(),
            $user->isJobSeeker() => $this->jobSeekerFeed($user),
            $user->isCompany() => $this->companyFeed($user),
            default => $this->adminFeed(),
        };
    }

    /**
     * The marketing landing page shown to visitors who are not signed in.
     */
    protected function publicLanding(): View
    {
        $publishedVacancies = JobVacancy::query()->published();

        return view('home.public', [
            'promos' => config('kalaluang.promos'),
            'categories' => Category::query()
                ->withCount(['jobVacancies as published_count' => fn (Builder $query) => $query->published()])
                ->orderByDesc('published_count')
                ->orderBy('name')
                ->get(),
            'latestVacancies' => $this->publicVacancies(),
            'entryProgramVacancies' => $this->publicVacancies(
                fn (Builder $query) => $query->whereIn('employment_type', [
                    EmploymentType::Internship,
                    EmploymentType::PartTime,
                ]),
            ),
            'featuredCompanies' => Company::query()
                ->verified()
                ->withCount(['jobVacancies as published_count' => fn (Builder $query) => $query->published()])
                ->whereHas('jobVacancies', fn (Builder $query) => $query->published())
                ->orderByDesc('published_count')
                ->orderBy('name')
                ->take(10)
                ->get(),
            'publishedCount' => (clone $publishedVacancies)->count(),
            'cityCount' => (clone $publishedVacancies)
                ->whereNotNull('location')
                ->distinct()
                ->count('location'),
            'categoryCount' => Category::query()->count(),
            'jobSeekerCount' => User::query()->where('role', UserRole::JobSeeker)->count(),

            // Angka nyata untuk bagian "Saringan 3 Lapis" — pembeda KalaLuang.
            'companySubmissionCount' => Company::query()->count(),
            'verifiedCompanyCount' => Company::query()->verified()->count(),
            'rejectedCompanyCount' => Company::query()
                ->whereIn('verification_status', [
                    CompanyVerificationStatus::Rejected,
                    CompanyVerificationStatus::Suspended,
                ])
                ->count(),
            'curatedVacancyCount' => JobVacancy::query()
                ->whereNotNull('published_at')
                ->count(),
            'rejectedVacancyCount' => JobVacancy::query()
                ->where('status', VacancyStatus::Rejected)
                ->count(),
            'reviewedReportCount' => VacancyReport::query()
                ->where('status', '!=', ReportStatus::Pending)
                ->count(),
            'takenDownVacancyCount' => JobVacancy::query()
                ->where('status', VacancyStatus::TakenDown)
                ->count(),
        ]);
    }

    /**
     * Personal feed for a signed-in job seeker: profile readiness, application
     * progress, and vacancies that match the education level they filled in.
     */
    protected function jobSeekerFeed(User $user): View
    {
        $profile = $user->jobSeekerProfile;

        $checklist = [
            'Data pendidikan' => filled($profile?->education_level) && filled($profile?->institution) && filled($profile?->major),
            'Kota domisili & no. HP' => filled($profile?->city) && filled($profile?->phone),
            'Keahlian' => (int) $profile?->skills()->count() > 0,
            'Unggah CV' => filled($profile?->cv_path),
        ];

        $applicationCounts = $user->applications()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $appliedIds = $user->applications()->pluck('job_vacancy_id');

        return view('home.job-seeker', [
            'profile' => $profile,
            'checklist' => $checklist,
            'isProfileComplete' => $profile?->isComplete() ?? false,
            'isEmailVerified' => $user->hasVerifiedEmail(),
            'applicationTotal' => (int) $applicationCounts->sum(),
            'newCount' => (int) ($applicationCounts[ApplicationStatus::New->value] ?? 0),
            'reviewedCount' => (int) ($applicationCounts[ApplicationStatus::Reviewed->value] ?? 0),
            'acceptedCount' => (int) ($applicationCounts[ApplicationStatus::Accepted->value] ?? 0),
            'savedCount' => $user->savedVacancies()->count(),
            'latestApplications' => $user->applications()
                ->with('jobVacancy.company')
                ->latest()
                ->take(3)
                ->get(),
            'matchedVacancies' => $this->publicVacancies(
                function (Builder $query) use ($profile, $appliedIds) {
                    $query->whereKeyNot($appliedIds);

                    if (filled($profile?->education_level)) {
                        $query->where('min_education', $profile->education_level);
                    }

                    if (filled($profile?->city)) {
                        $query->where(fn (Builder $match) => $match
                            ->where('location', 'like', '%'.$profile->city.'%')
                            ->orWhere('is_remote', true));
                    }
                },
                limit: 4,
            ),
            'newestVacancies' => $this->publicVacancies(
                fn (Builder $query) => $query->whereKeyNot($appliedIds),
                limit: 4,
            ),
        ]);
    }

    /**
     * Shortcut feed for a signed-in company account.
     */
    protected function companyFeed(User $user): View
    {
        $company = $user->company;

        return view('home.company', [
            'company' => $company,
            'activeCount' => $company?->jobVacancies()->published()->count() ?? 0,
            'underReviewCount' => $company?->jobVacancies()
                ->whereIn('status', [VacancyStatus::UnderReview, VacancyStatus::PendingPayment])
                ->count() ?? 0,
            'unpaidCount' => $company?->payments()
                ->whereIn('status', [PaymentStatus::Unpaid, PaymentStatus::Rejected])
                ->count() ?? 0,
            'newApplicantCount' => $company === null ? 0 : $company->jobVacancies()
                ->withCount(['applications as new_count' => fn (Builder $query) => $query->where('status', ApplicationStatus::New)])
                ->get()
                ->sum('new_count'),
        ]);
    }

    /**
     * Shortcut feed for an admin account: everything still waiting in a queue.
     */
    protected function adminFeed(): View
    {
        return view('home.admin', [
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
        ]);
    }

    /**
     * Vacancies from the public catalogue, newest first.
     *
     * @param  (callable(Builder): mixed)|null  $filter
     * @return Collection<int, JobVacancy>
     */
    protected function publicVacancies(?callable $filter = null, int $limit = 8): Collection
    {
        return JobVacancy::query()
            ->published()
            ->with(['company', 'category'])
            ->when($filter !== null, $filter)
            ->latest('published_at')
            ->take($limit)
            ->get();
    }
}
