<?php

namespace App\Models;

use Database\Factories\SavedVacancyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'job_vacancy_id'])]
class SavedVacancy extends Model
{
    /** @use HasFactory<SavedVacancyFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobVacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class);
    }
}
