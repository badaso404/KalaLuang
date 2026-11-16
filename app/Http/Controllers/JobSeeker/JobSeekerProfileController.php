<?php

namespace App\Http\Controllers\JobSeeker;

use App\Enums\EducationLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\JobSeekerProfileRequest;
use App\Models\Skill;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class JobSeekerProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $profile = $request->user()->jobSeekerProfile ?? $request->user()->jobSeekerProfile()->create();

        return view('job-seeker.profile', [
            'profile' => $profile->load('skills'),
            'skillGroups' => $this->skillGroups(),
            'educationLevels' => EducationLevel::cases(),
            'selectedSkills' => $profile->skills->pluck('id')->all(),
        ]);
    }

    /**
     * The catalogue is long, so the form lists it per sector, alphabetically,
     * with the skills job seekers typed themselves — uncategorised — grouped
     * into a single last section.
     *
     * @return Collection<string, Collection<int, Skill>>
     */
    protected function skillGroups(): Collection
    {
        return Skill::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Skill $skill): string => $skill->category ?? Skill::OtherCategory)
            ->sortKeysUsing(function (string $first, string $second): int {
                return [$first === Skill::OtherCategory, $first] <=> [$second === Skill::OtherCategory, $second];
            });
    }

    public function update(JobSeekerProfileRequest $request): RedirectResponse
    {
        $profile = $request->user()->jobSeekerProfile;
        $attributes = $request->safe()->except(['skills', 'custom_skills', 'cv']);

        if ($request->hasFile('cv')) {
            if ($profile->cv_path !== null && ! $this->cvIsAttachedToAnApplication($request, $profile->cv_path)) {
                Storage::disk('local')->delete($profile->cv_path);
            }

            $attributes['cv_path'] = $request->file('cv')->store('cv', 'local');
        }

        $profile->update($attributes);
        $profile->skills()->sync($this->skillIds($request));

        return back()->with('status', $profile->isComplete()
            ? 'Profil tersimpan. Profil Anda sudah lengkap dan siap digunakan untuk melamar.'
            : 'Profil tersimpan. Lengkapi pendidikan dan unggah CV agar dapat melamar.');
    }

    /**
     * Skills picked from the catalogue arrive as ids, while skills the job
     * seeker typed arrive as names and join the catalogue the first time
     * anyone names them.
     *
     * @return list<int>
     */
    protected function skillIds(JobSeekerProfileRequest $request): array
    {
        return collect($request->validated('skills') ?? [])
            ->merge(collect($request->validated('custom_skills') ?? [])
                ->map(fn (string $name): int => Skill::findOrCreateByName($name)->id))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * An application stores the CV path it was sent with, so replacing the CV
     * must not delete a file a company still needs to read.
     */
    protected function cvIsAttachedToAnApplication(Request $request, string $path): bool
    {
        return $request->user()->applications()->where('cv_path', $path)->exists();
    }
}
