<?php

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name'])]
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    public function jobSeekerProfiles(): BelongsToMany
    {
        return $this->belongsToMany(JobSeekerProfile::class, 'job_seeker_skill');
    }
}
