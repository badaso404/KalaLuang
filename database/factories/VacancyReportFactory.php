<?php

namespace Database\Factories;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\JobVacancy;
use App\Models\User;
use App\Models\VacancyReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VacancyReport>
 */
class VacancyReportFactory extends Factory
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
            'reporter_id' => User::factory()->jobSeeker(),
            'reason' => fake()->randomElement(ReportReason::cases()),
            'description' => fake()->sentence(),
            'status' => ReportStatus::Pending,
        ];
    }
}
