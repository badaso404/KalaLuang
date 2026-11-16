<?php

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'category'])]
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    /**
     * The group a skill typed by a job seeker is shown under.
     */
    public const string OtherCategory = 'Lainnya';

    /**
     * Resolve a skill a job seeker typed themselves, reusing the catalogue
     * entry when one already exists under any casing or spacing.
     */
    public static function findOrCreateByName(string $name): self
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '');

        return static::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first()
            ?? static::query()->create(['name' => $name]);
    }

    public function jobSeekerProfiles(): BelongsToMany
    {
        return $this->belongsToMany(JobSeekerProfile::class, 'job_seeker_skill');
    }
}
