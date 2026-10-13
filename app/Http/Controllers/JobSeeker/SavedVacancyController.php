<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavedVacancyController extends Controller
{
    public function index(Request $request): View
    {
        return view('job-seeker.saved', [
            'saved' => $request->user()
                ->savedVacancies()
                ->with('jobVacancy.company', 'jobVacancy.category')
                ->latest()
                ->paginate(10),
        ]);
    }

    public function store(Request $request, JobVacancy $vacancy): RedirectResponse
    {
        $request->user()->savedVacancies()->firstOrCreate([
            'job_vacancy_id' => $vacancy->id,
        ]);

        return back()->with('status', 'Lowongan disimpan.');
    }

    public function destroy(Request $request, JobVacancy $vacancy): RedirectResponse
    {
        $request->user()->savedVacancies()
            ->where('job_vacancy_id', $vacancy->id)
            ->delete();

        return back()->with('status', 'Lowongan dihapus dari daftar tersimpan.');
    }
}
