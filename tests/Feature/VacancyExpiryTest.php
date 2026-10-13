<?php

namespace Tests\Feature;

use App\Enums\VacancyStatus;
use App\Models\JobVacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_scheduled_command_expires_vacancies_past_their_window(): void
    {
        $overdue = JobVacancy::factory()->overdue()->create();
        $live = JobVacancy::factory()->published()->create();
        $underReview = JobVacancy::factory()->underReview()->create();

        $this->artisan('vacancies:expire')
            ->expectsOutputToContain('1 lowongan ditandai kedaluwarsa.')
            ->assertSuccessful();

        $this->assertSame(VacancyStatus::Expired, $overdue->refresh()->status);
        $this->assertSame(VacancyStatus::Active, $live->refresh()->status);
        $this->assertSame(VacancyStatus::UnderReview, $underReview->refresh()->status);
    }

    public function test_an_overdue_vacancy_is_already_hidden_from_the_catalogue(): void
    {
        JobVacancy::factory()->overdue()->create();

        $this->assertSame(0, JobVacancy::published()->count());
    }

    public function test_the_command_is_safe_to_run_when_nothing_is_due(): void
    {
        JobVacancy::factory()->published()->create();

        $this->artisan('vacancies:expire')
            ->expectsOutputToContain('0 lowongan ditandai kedaluwarsa.')
            ->assertSuccessful();
    }
}
