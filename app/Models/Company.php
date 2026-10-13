<?php

namespace App\Models;

use App\Enums\CompanyVerificationStatus;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'name',
    'slug',
    'industry',
    'city',
    'address',
    'website',
    'description',
    'logo_path',
    'verification_status',
    'verification_note',
    'verified_at',
    'verified_by',
])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The model's default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'verification_status' => CompanyVerificationStatus::Pending->value,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'verification_status' => CompanyVerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CompanyDocument::class);
    }

    public function jobVacancies(): HasMany
    {
        return $this->hasMany(JobVacancy::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Limit the query to companies that passed admin verification.
     */
    #[Scope]
    protected function verified(Builder $query): Builder
    {
        return $query->where('verification_status', CompanyVerificationStatus::Verified);
    }

    /**
     * Determine whether the company may post vacancies (BR-02).
     */
    public function isVerified(): bool
    {
        return $this->verification_status === CompanyVerificationStatus::Verified;
    }
}
