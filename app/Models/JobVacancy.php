<?php

namespace App\Models;

use App\Enums\EducationLevel;
use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use Database\Factories\JobVacancyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'category_id',
    'title',
    'slug',
    'employment_type',
    'location',
    'is_remote',
    'min_education',
    'description',
    'requirements',
    'how_to_apply',
    'salary_min',
    'salary_max',
    'requires_experience',
    'status',
    'review_note',
    'published_at',
    'expires_at',
])]
class JobVacancy extends Model
{
    /** @use HasFactory<JobVacancyFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The model's default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => VacancyStatus::Draft->value,
        'is_remote' => false,
        'requires_experience' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'min_education' => EducationLevel::class,
            'status' => VacancyStatus::class,
            'is_remote' => 'boolean',
            'requires_experience' => 'boolean',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function savedByUsers(): HasMany
    {
        return $this->hasMany(SavedVacancy::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(VacancyReport::class);
    }

    /**
     * Limit the query to vacancies that belong in the public catalogue (BR-03).
     */
    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('status', VacancyStatus::Active)
            ->where('expires_at', '>', now())
            ->whereHas('company', fn (Builder $company) => $company->verified());
    }

    /**
     * Limit the query to vacancies whose paid listing window has run out.
     */
    #[Scope]
    protected function dueForExpiry(Builder $query): Builder
    {
        return $query->where('status', VacancyStatus::Active)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Whether the company may still edit the vacancy's content.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [
            VacancyStatus::Draft,
            VacancyStatus::PendingPayment,
            VacancyStatus::UnderReview,
            VacancyStatus::Rejected,
        ], strict: true);
    }

    /**
     * Publish the vacancy for one paid listing window (30 days by default).
     */
    public function publish(): void
    {
        $publishedAt = now();

        $this->update([
            'status' => VacancyStatus::Active,
            'review_note' => null,
            'published_at' => $publishedAt,
            'expires_at' => $publishedAt->copy()->addDays(config('kalaluang.listing_duration_days')),
        ]);
    }
}
