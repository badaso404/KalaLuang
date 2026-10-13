<?php

namespace Database\Factories;

use App\Models\JobVacancy;
use App\Models\SavedVacancy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedVacancy>
 */
class SavedVacancyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->jobSeeker(),
            'job_vacancy_id' => JobVacancy::factory(),
        ];
    }
}
