<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VacancyStatus;
use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VacancyCurationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(VacancyStatus::class)],
        ]);

        return view('admin.curation.index', [
            'vacancies' => JobVacancy::query()
                ->with(['company', 'category'])
                ->when(
                    $filters['status'] ?? null,
                    fn ($query, string $status) => $query->where('status', $status),
                    fn ($query) => $query->where('status', VacancyStatus::UnderReview),
                )
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'statuses' => VacancyStatus::cases(),
            'filters' => $filters,
        ]);
    }

    public function show(JobVacancy $vacancy): View
    {
        return view('admin.curation.show', [
            'vacancy' => $vacancy->load('company', 'category', 'payments'),
        ]);
    }

    /**
     * Curation passed: publish for one listing window.
     */
    public function approve(JobVacancy $vacancy): RedirectResponse
    {
        abort_unless($vacancy->status === VacancyStatus::UnderReview, 403);

        $vacancy->publish();

        return redirect()
            ->route('admin.curation.index')
            ->with('status', '"'.$vacancy->title.'" kini tayang hingga '.$vacancy->expires_at->translatedFormat('d F Y').'.');
    }

    public function reject(Request $request, JobVacancy $vacancy): RedirectResponse
    {
        abort_unless($vacancy->status === VacancyStatus::UnderReview, 403);

        $validated = $request->validate([
            'review_note' => ['required', 'string', 'max:1000'],
        ], [], ['review_note' => 'catatan kurasi']);

        $vacancy->update([
            'status' => VacancyStatus::Rejected,
            'review_note' => $validated['review_note'],
        ]);

        return redirect()
            ->route('admin.curation.index')
            ->with('status', '"'.$vacancy->title.'" ditolak. Perusahaan dapat merevisi tanpa bayar ulang.');
    }
}
