<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        return view('job-seeker.applications', [
            'applications' => $request->user()
                ->applications()
                ->with('jobVacancy.company')
                ->latest()
                ->paginate(10),
        ]);
    }

    /**
     * Apply to a vacancy. Requires a complete profile (BR-06) and allows one
     * application per vacancy (BR-05). The CV is snapshotted onto the application.
     */
    public function store(Request $request, JobVacancy $vacancy): RedirectResponse
    {
        abort_unless(
            JobVacancy::query()->published()->whereKey($vacancy->getKey())->exists(),
            404,
        );

        $user = $request->user();
        $profile = $user->jobSeekerProfile;

        if ($profile === null || ! $profile->isComplete()) {
            return redirect()->route('job-seeker.profile.edit')->with(
                'error',
                'Lengkapi data pendidikan dan unggah CV sebelum melamar.',
            );
        }

        if ($vacancy->applications()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Anda sudah pernah melamar lowongan ini.');
        }

        $validated = $request->validate([
            'cover_letter' => ['nullable', 'string', 'max:2000'],
        ], [], ['cover_letter' => 'surat lamaran']);

        $vacancy->applications()->create([
            ...$validated,
            'user_id' => $user->id,
            'cv_path' => $profile->cv_path,
        ]);

        return redirect()
            ->route('job-seeker.applications.index')
            ->with('status', 'Lamaran Anda berhasil dikirim.');
    }
}
