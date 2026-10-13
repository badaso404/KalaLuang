<?php

namespace Database\Factories;

use App\Enums\EducationLevel;
use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Models\Category;
use App\Models\Company;
use App\Models\JobVacancy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<JobVacancy>
 */
class JobVacancyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->jobTitle().' Junior';

        return [
            'company_id' => Company::factory()->verified(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'employment_type' => fake()->randomElement(EmploymentType::cases()),
            'location' => fake()->city(),
            'is_remote' => false,
            'min_education' => EducationLevel::D3,
            'description' => fake()->paragraph(),
            'requirements' => "Lulusan D3 atau S1\nFresh graduate, tidak wajib pengalaman kerja",
            'how_to_apply' => 'Klik Lamar Sekarang.',
            'salary_min' => 4_000_000,
            'salary_max' => 5_000_000,
            'requires_experience' => false,
            'status' => VacancyStatus::Draft,
        ];
    }

    /**
     * A vacancy that belongs in the public catalogue (BR-03).
     */
    public function published(): static
    {
        return $this->state([
            'status' => VacancyStatus::Active,
            'published_at' => now()->subDay(),
            'expires_at' => now()->addDays(config('kalaluang.listing_duration_days')),
        ]);
    }

    public function underReview(): static
    {
        return $this->state(['status' => VacancyStatus::UnderReview]);
    }

    public function pendingPayment(): static
    {
        return $this->state(['status' => VacancyStatus::PendingPayment]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => VacancyStatus::Rejected,
            'review_note' => 'Masih mensyaratkan pengalaman.',
        ]);
    }

    /**
     * Still marked active, but past its paid window.
     */
    public function overdue(): static
    {
        return $this->state([
            'status' => VacancyStatus::Active,
            'published_at' => now()->subDays(40),
            'expires_at' => now()->subDay(),
        ]);
    }

    public function expired(): static
    {
        return $this->state([
            'status' => VacancyStatus::Expired,
            'published_at' => now()->subDays(40),
            'expires_at' => now()->subDays(10),
        ]);
    }

    public function remote(): static
    {
        return $this->state([
            'is_remote' => true,
            'location' => null,
        ]);
    }
}
