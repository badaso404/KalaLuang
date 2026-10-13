<?php

namespace App\Http\Controllers\JobSeeker;

use App\Enums\EducationLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\JobSeekerProfileRequest;
use App\Models\Skill;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobSeekerProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $profile = $request->user()->jobSeekerProfile ?? $request->user()->jobSeekerProfile()->create();

        return view('job-seeker.profile', [
            'profile' => $profile->load('skills'),
            'skills' => Skill::query()->orderBy('name')->get(),
            'educationLevels' => EducationLevel::cases(),
            'selectedSkills' => $profile->skills->pluck('id')->all(),
        ]);
    }

    public function update(JobSeekerProfileRequest $request): RedirectResponse
    {
        $profile = $request->user()->jobSeekerProfile;
        $attributes = $request->safe()->except(['skills', 'cv']);

        if ($request->hasFile('cv')) {
            if ($profile->cv_path !== null) {
                Storage::disk('public')->delete($profile->cv_path);
            }

            $attributes['cv_path'] = $request->file('cv')->store('cv', 'public');
        }

        $profile->update($attributes);
        $profile->skills()->sync($request->validated('skills') ?? []);

        return back()->with('status', $profile->isComplete()
            ? 'Profil tersimpan. Profil Anda sudah lengkap dan siap digunakan untuk melamar.'
            : 'Profil tersimpan. Lengkapi pendidikan dan unggah CV agar dapat melamar.');
    }
}
