<?php

namespace App\Http\Controllers\Company;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApplicantController extends Controller
{
    public function index(Request $request): View
    {
        $company = $request->user()->company;

        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(ApplicationStatus::class)],
            'vacancy' => ['nullable', 'integer'],
        ]);

        $applications = Application::query()
            ->whereHas('jobVacancy', fn (Builder $query) => $query->where('company_id', $company->id))
            ->with(['user.jobSeekerProfile', 'jobVacancy'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['vacancy'] ?? null, fn (Builder $query, int $id) => $query->where('job_vacancy_id', $id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('company.applicants.index', [
            'applications' => $applications,
            'statuses' => ApplicationStatus::cases(),
            'vacancies' => $company->jobVacancies()->orderBy('title')->get(['id', 'title']),
            'filters' => $filters,
        ]);
    }

    public function show(Application $application): View
    {
        $this->authorize('manage', $application);

        return view('company.applicants.show', [
            'application' => $application->load(['user.jobSeekerProfile.skills', 'jobVacancy']),
            'statuses' => ApplicationStatus::cases(),
        ]);
    }

    /**
     * Move the applicant along the pipeline and notify them.
     */
    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('manage', $application);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
        ], [], ['status' => 'status lamaran']);

        $application->update([
            'status' => $validated['status'],
            'status_updated_at' => now(),
        ]);

        $application->user->notify(new ApplicationStatusChanged($application));

        return back()->with('status', 'Status pelamar diperbarui menjadi '.$application->status->label().'.');
    }
}
