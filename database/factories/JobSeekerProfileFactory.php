<?php

namespace Database\Factories;

use App\Enums\EducationLevel;
use App\Models\JobSeekerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobSeekerProfile>
 */
class JobSeekerProfileFactory extends Factory
{
    /**
     * A complete profile by default, so applying works without extra setup.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->jobSeeker(),
            'phone' => '0812'.fake()->numerify('########'),
            'city' => fake()->city(),
            'education_level' => fake()->randomElement(EducationLevel::cases()),
            'institution' => 'Universitas '.fake()->city(),
            'major' => fake()->randomElement(['Manajemen', 'Akuntansi', 'Sistem Informasi', 'Ilmu Komunikasi']),
            'graduation_year' => fake()->numberBetween(2023, 2026),
            'bio' => fake()->sentence(),
            'portfolio_url' => 'https://'.fake()->unique()->domainName(),
            'cv_path' => 'cv/'.fake()->unique()->slug().'.pdf',
        ];
    }

    /**
     * A profile that fails the completeness check (BR-06).
     */
    public function incomplete(): static
    {
        return $this->state([
            'education_level' => null,
            'institution' => null,
            'major' => null,
            'cv_path' => null,
        ]);
    }

    public function withoutCv(): static
    {
        return $this->state(['cv_path' => null]);
    }
}
