<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\VacancyStatus;
use App\Models\Category;
use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyVacancyPostingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    protected function validPayload(array $overrides = []): array
    {
        return [
            'title' => 'Staf Administrasi Junior',
            'category_id' => Category::factory()->create()->id,
            'employment_type' => 'full_time',
            'location' => 'Jakarta',
            'min_education' => 'D3',
            'description' => 'Mendukung administrasi harian kantor.',
            'requirements' => "Lulusan D3 atau S1\nFresh graduate, tidak wajib pengalaman kerja",
            'how_to_apply' => 'Klik Lamar Sekarang.',
            'salary_min' => 4_000_000,
            'salary_max' => 5_000_000,
            'entry_level_declaration' => '1',
            ...$overrides,
        ];
    }

    public function test_a_verified_company_posting_a_vacancy_gets_an_invoice(): void
    {
        $company = Company::factory()->verified()->create();

        $this->actingAs($company->user)
            ->post(route('company.vacancies.store'), $this->validPayload())
            ->assertRedirect();

        $vacancy = JobVacancy::sole();
        $payment = Payment::sole();

        $this->assertSame(VacancyStatus::PendingPayment, $vacancy->status);
        $this->assertFalse($vacancy->requires_experience);
        $this->assertNull($vacancy->published_at);

        $this->assertSame(PaymentStatus::Unpaid, $payment->status);
        $this->assertSame(config('kalaluang.listing_price'), $payment->amount);
        $this->assertSame($vacancy->id, $payment->job_vacancy_id);
    }

    public function test_an_unverified_company_cannot_reach_the_posting_form(): void
    {
        foreach ([Company::factory()->create(), Company::factory()->rejected()->create()] as $company) {
            $this->actingAs($company->user)
                ->get(route('company.vacancies.create'))
                ->assertRedirect(route('company.verification.show'));

            $this->actingAs($company->user)
                ->post(route('company.vacancies.store'), $this->validPayload())
                ->assertRedirect(route('company.verification.show'));
        }

        $this->assertDatabaseCount('job_vacancies', 0);
    }

    public function test_the_entry_level_declaration_is_mandatory(): void
    {
        $company = Company::factory()->verified()->create();

        $this->actingAs($company->user)
            ->post(route('company.vacancies.store'), $this->validPayload(['entry_level_declaration' => null]))
            ->assertSessionHasErrors('entry_level_declaration');

        $this->assertDatabaseCount('job_vacancies', 0);
    }

    public function test_location_is_required_unless_the_vacancy_is_remote(): void
    {
        $company = Company::factory()->verified()->create();

        $this->actingAs($company->user)
            ->post(route('company.vacancies.store'), $this->validPayload(['location' => null]))
            ->assertSessionHasErrors('location');

        $this->actingAs($company->user)
            ->post(route('company.vacancies.store'), $this->validPayload([
                'location' => null,
                'is_remote' => '1',
            ]))
            ->assertSessionHasNoErrors();

        $vacancy = JobVacancy::sole();

        $this->assertTrue($vacancy->is_remote);
        $this->assertNull($vacancy->location);
    }

    public function test_salary_max_cannot_be_lower_than_salary_min(): void
    {
        $company = Company::factory()->verified()->create();

        $this->actingAs($company->user)
            ->post(route('company.vacancies.store'), $this->validPayload([
                'salary_min' => 5_000_000,
                'salary_max' => 3_000_000,
            ]))
            ->assertSessionHasErrors('salary_max');
    }

    public function test_uploading_a_payment_proof_moves_the_invoice_to_waiting_confirmation(): void
    {
        Storage::fake('public');

        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->pendingPayment()->for($company)->create();
        $payment = Payment::factory()->for($vacancy, 'jobVacancy')->create(['company_id' => $company->id]);

        $this->actingAs($company->user)
            ->post(route('company.payments.proof', $payment), [
                'proof' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertRedirect();

        $payment->refresh();

        $this->assertSame(PaymentStatus::WaitingConfirmation, $payment->status);
        Storage::disk('public')->assertExists($payment->proof_path);
    }

    public function test_a_company_cannot_open_another_companys_invoice(): void
    {
        $owner = Company::factory()->verified()->create();
        $intruder = Company::factory()->verified()->create();

        $vacancy = JobVacancy::factory()->pendingPayment()->for($owner)->create();
        $payment = Payment::factory()->for($vacancy, 'jobVacancy')->create(['company_id' => $owner->id]);

        $this->actingAs($intruder->user)
            ->get(route('company.payments.show', $payment))
            ->assertForbidden();
    }

    public function test_a_rejected_vacancy_can_be_resubmitted_without_paying_again(): void
    {
        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->rejected()->for($company)->create();
        Payment::factory()->paid()->for($vacancy, 'jobVacancy')->create(['company_id' => $company->id]);

        $this->actingAs($company->user)
            ->post(route('company.vacancies.resubmit', $vacancy))
            ->assertRedirect();

        $this->assertSame(VacancyStatus::UnderReview, $vacancy->refresh()->status);
        $this->assertNull($vacancy->review_note);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_renewing_an_expired_vacancy_raises_a_second_invoice(): void
    {
        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->expired()->for($company)->create();
        Payment::factory()->paid()->for($vacancy, 'jobVacancy')->create(['company_id' => $company->id]);

        $this->actingAs($company->user)
            ->post(route('company.vacancies.renew', $vacancy))
            ->assertRedirect();

        $this->assertSame(VacancyStatus::PendingPayment, $vacancy->refresh()->status);
        $this->assertDatabaseCount('payments', 2);
        $this->assertSame(PaymentStatus::Unpaid, $vacancy->payments()->latest('id')->first()->status);
    }

    public function test_an_active_vacancy_can_be_closed_early(): void
    {
        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->published()->for($company)->create();

        $this->actingAs($company->user)
            ->post(route('company.vacancies.close', $vacancy))
            ->assertRedirect();

        $this->assertSame(VacancyStatus::Closed, $vacancy->refresh()->status);
    }

    public function test_a_company_cannot_manage_a_vacancy_it_does_not_own(): void
    {
        $vacancy = JobVacancy::factory()->published()->create();
        $intruder = Company::factory()->verified()->create();

        $this->actingAs($intruder->user)
            ->get(route('company.vacancies.show', $vacancy))
            ->assertForbidden();

        $this->actingAs($intruder->user)
            ->post(route('company.vacancies.close', $vacancy))
            ->assertForbidden();
    }

    public function test_an_active_vacancy_cannot_be_edited(): void
    {
        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->published()->for($company)->create();

        $this->actingAs($company->user)
            ->get(route('company.vacancies.edit', $vacancy))
            ->assertForbidden();
    }
}
