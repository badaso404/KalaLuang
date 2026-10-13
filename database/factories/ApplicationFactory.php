<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_vacancy_id' => JobVacancy::factory()->published(),
            'user_id' => User::factory()->jobSeeker(),
            'cv_path' => 'cv/'.fake()->unique()->slug().'.pdf',
            'cover_letter' => fake()->sentence(),
            'status' => ApplicationStatus::New,
        ];
    }

    public function reviewed(): static
    {
        return $this->state([
            'status' => ApplicationStatus::Reviewed,
            'status_updated_at' => now(),
        ]);
    }
}
