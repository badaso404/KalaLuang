<?php

namespace App\Models;

use App\Enums\EducationLevel;
use Database\Factories\JobSeekerProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'user_id',
    'phone',
    'city',
    'education_level',
    'institution',
    'major',
    'graduation_year',
    'bio',
    'portfolio_url',
    'cv_path',
])]
class JobSeekerProfile extends Model
{
    /** @use HasFactory<JobSeekerProfileFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'education_level' => EducationLevel::class,
            'graduation_year' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'job_seeker_skill');
    }

    /**
     * Determine whether the profile holds everything required to apply (BR-06).
     */
    public function isComplete(): bool
    {
        return filled($this->education_level)
            && filled($this->institution)
            && filled($this->major)
            && filled($this->cv_path);
    }
}
