<?php

namespace Tests\Feature;

use App\Enums\CompanyVerificationStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReportStatus;
use App\Enums\VacancyStatus;
use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\Payment;
use App\Models\User;
use App\Models\VacancyReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_an_admin_verifies_a_company(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.companies.verify', $company))
            ->assertRedirect();

        $company->refresh();

        $this->assertSame(CompanyVerificationStatus::Verified, $company->verification_status);
        $this->assertNotNull($company->verified_at);
        $this->assertSame($this->admin->id, $company->verified_by);
    }

    public function test_rejecting_a_company_requires_a_note_and_allows_resubmission(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.companies.reject', $company))
            ->assertSessionHasErrors('verification_note');

        $this->actingAs($this->admin)
            ->post(route('admin.companies.reject', $company), [
                'verification_note' => 'NIB tidak terbaca.',
            ])
            ->assertRedirect();

        $company->refresh();

        $this->assertSame(CompanyVerificationStatus::Rejected, $company->verification_status);
        $this->assertSame('NIB tidak terbaca.', $company->verification_note);
    }

    public function test_suspending_a_company_takes_down_all_of_its_active_vacancies(): void
    {
        $company = Company::factory()->verified()->create();
        $active = JobVacancy::factory()->published()->for($company)->create();
        $closed = JobVacancy::factory()->for($company)->create(['status' => VacancyStatus::Closed]);

        $this->actingAs($this->admin)
            ->post(route('admin.companies.suspend', $company), [
                'verification_note' => 'Terbukti meminta biaya dari pelamar.',
            ])
            ->assertRedirect();

        $this->assertSame(CompanyVerificationStatus::Suspended, $company->refresh()->verification_status);
        $this->assertSame(VacancyStatus::TakenDown, $active->refresh()->status);
        $this->assertSame(VacancyStatus::Closed, $closed->refresh()->status);
        $this->assertSame(0, JobVacancy::published()->count());
    }

    public function test_confirming_a_payment_sends_the_vacancy_to_curation(): void
    {
        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->pendingPayment()->for($company)->create();
        $payment = Payment::factory()->waitingConfirmation()
            ->for($vacancy, 'jobVacancy')
            ->create(['company_id' => $company->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.confirm', $payment))
            ->assertRedirect();

        $payment->refresh();

        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame($this->admin->id, $payment->confirmed_by);
        $this->assertSame(VacancyStatus::UnderReview, $vacancy->refresh()->status);
    }

    public function test_rejecting_a_payment_leaves_the_vacancy_unpublished(): void
    {
        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->pendingPayment()->for($company)->create();
        $payment = Payment::factory()->waitingConfirmation()
            ->for($vacancy, 'jobVacancy')
            ->create(['company_id' => $company->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.reject', $payment))
            ->assertRedirect();

        $this->assertSame(PaymentStatus::Rejected, $payment->refresh()->status);
        $this->assertSame(VacancyStatus::PendingPayment, $vacancy->refresh()->status);
    }

    public function test_approving_curation_publishes_the_vacancy_for_the_configured_window(): void
    {
        $vacancy = JobVacancy::factory()->underReview()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.curation.approve', $vacancy))
            ->assertRedirect(route('admin.curation.index'));

        $vacancy->refresh();

        $this->assertSame(VacancyStatus::Active, $vacancy->status);
        $this->assertNotNull($vacancy->published_at);
        $this->assertSame(
            config('kalaluang.listing_duration_days'),
            (int) $vacancy->published_at->diffInDays($vacancy->expires_at, true),
        );
        $this->assertSame(1, JobVacancy::published()->count());
    }

    public function test_rejecting_curation_requires_a_note(): void
    {
        $vacancy = JobVacancy::factory()->underReview()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.curation.reject', $vacancy))
            ->assertSessionHasErrors('review_note');

        $this->actingAs($this->admin)
            ->post(route('admin.curation.reject', $vacancy), [
                'review_note' => 'Masih mensyaratkan pengalaman.',
            ])
            ->assertRedirect();

        $vacancy->refresh();

        $this->assertSame(VacancyStatus::Rejected, $vacancy->status);
        $this->assertSame('Masih mensyaratkan pengalaman.', $vacancy->review_note);
    }

    public function test_a_vacancy_not_under_review_cannot_be_approved(): void
    {
        $vacancy = JobVacancy::factory()->pendingPayment()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.curation.approve', $vacancy))
            ->assertForbidden();

        $this->assertSame(VacancyStatus::PendingPayment, $vacancy->refresh()->status);
    }

    public function test_upholding_a_report_takes_the_vacancy_down(): void
    {
        $vacancy = JobVacancy::factory()->published()->create();
        $report = VacancyReport::factory()->for($vacancy, 'jobVacancy')->create();

        $this->actingAs($this->admin)
            ->post(route('admin.reports.uphold', $report), ['admin_note' => 'Terbukti meminta biaya.'])
            ->assertRedirect(route('admin.reports.index'));

        $report->refresh();

        $this->assertSame(ReportStatus::Valid, $report->status);
        $this->assertSame($this->admin->id, $report->reviewed_by);
        $this->assertNotNull($report->reviewed_at);
        $this->assertSame(VacancyStatus::TakenDown, $vacancy->refresh()->status);
        $this->assertSame(0, JobVacancy::published()->count());
    }

    public function test_dismissing_a_report_leaves_the_vacancy_live(): void
    {
        $vacancy = JobVacancy::factory()->published()->create();
        $report = VacancyReport::factory()->for($vacancy, 'jobVacancy')->create();

        $this->actingAs($this->admin)
            ->post(route('admin.reports.dismiss', $report))
            ->assertRedirect(route('admin.reports.index'));

        $this->assertSame(ReportStatus::Dismissed, $report->refresh()->status);
        $this->assertSame(VacancyStatus::Active, $vacancy->refresh()->status);
        $this->assertSame(1, JobVacancy::published()->count());
    }

    public function test_a_report_can_only_be_reviewed_once(): void
    {
        $report = VacancyReport::factory()->create(['status' => ReportStatus::Dismissed]);

        $this->actingAs($this->admin)
            ->post(route('admin.reports.uphold', $report))
            ->assertForbidden();
    }

    public function test_admin_panel_pages_load(): void
    {
        $company = Company::factory()->create();
        $vacancy = JobVacancy::factory()->underReview()->create();
        $payment = Payment::factory()->waitingConfirmation()->create();
        $report = VacancyReport::factory()->create();

        $pages = [
            route('admin.dashboard'),
            route('admin.companies.index'),
            route('admin.companies.show', $company),
            route('admin.payments.index'),
            route('admin.curation.index'),
            route('admin.curation.show', $vacancy),
            route('admin.reports.index'),
            route('admin.reports.show', $report),
        ];

        foreach ($pages as $page) {
            $this->actingAs($this->admin)->get($page)->assertOk();
        }

        $this->assertNotNull($payment);
    }
}
