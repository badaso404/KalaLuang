<?php

namespace Tests\Feature;

use App\Enums\ReportStatus;
use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\User;
use App\Models\VacancyReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_job_seeker_can_report_a_vacancy(): void
    {
        $seeker = User::factory()->jobSeeker()->create();
        $vacancy = JobVacancy::factory()->published()->create();

        $this->actingAs($seeker)
            ->post(route('vacancies.report', $vacancy), [
                'reason' => 'asks_fee',
                'description' => 'Diminta bayar biaya administrasi.',
            ])
            ->assertRedirect();

        $report = VacancyReport::sole();

        $this->assertSame($seeker->id, $report->reporter_id);
        $this->assertSame(ReportStatus::Pending, $report->status);
    }

    public function test_a_company_can_report_another_companys_vacancy(): void
    {
        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->published()->create();

        $this->actingAs($company->user)
            ->post(route('vacancies.report', $vacancy), ['reason' => 'fake_company'])
            ->assertRedirect();

        $this->assertDatabaseCount('vacancy_reports', 1);
    }

    public function test_a_company_cannot_report_its_own_vacancy(): void
    {
        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->published()->for($company)->create();

        $this->actingAs($company->user)
            ->post(route('vacancies.report', $vacancy), ['reason' => 'misleading'])
            ->assertForbidden();

        $this->assertDatabaseCount('vacancy_reports', 0);
    }

    public function test_a_user_can_only_report_a_vacancy_once(): void
    {
        $seeker = User::factory()->jobSeeker()->create();
        $vacancy = JobVacancy::factory()->published()->create();

        $this->actingAs($seeker)
            ->post(route('vacancies.report', $vacancy), ['reason' => 'misleading'])
            ->assertRedirect();

        $this->actingAs($seeker)
            ->post(route('vacancies.report', $vacancy), ['reason' => 'asks_fee'])
            ->assertForbidden();

        $this->assertDatabaseCount('vacancy_reports', 1);
    }

    public function test_the_reason_must_be_a_known_option(): void
    {
        $seeker = User::factory()->jobSeeker()->create();
        $vacancy = JobVacancy::factory()->published()->create();

        $this->actingAs($seeker)
            ->post(route('vacancies.report', $vacancy), ['reason' => 'alasan-palsu'])
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('vacancy_reports', 0);
    }

    public function test_guests_cannot_report(): void
    {
        $vacancy = JobVacancy::factory()->published()->create();

        $this->post(route('vacancies.report', $vacancy), ['reason' => 'misleading'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('vacancy_reports', 0);
    }

    public function test_an_admin_cannot_file_a_report(): void
    {
        $admin = User::factory()->admin()->create();
        $vacancy = JobVacancy::factory()->published()->create();

        $this->actingAs($admin)
            ->post(route('vacancies.report', $vacancy), ['reason' => 'misleading'])
            ->assertForbidden();
    }
}
