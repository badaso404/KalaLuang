<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\JobSeekerProfile;
use App\Models\JobVacancy;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateFileAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    protected function putFile(string $path): void
    {
        Storage::disk('local')->put($path, 'berkas-palsu');
    }

    /**
     * The private disk is only served through signed URLs, which the
     * application never hands out, so guessing a path leads nowhere.
     */
    public function test_a_cv_is_not_reachable_by_guessing_its_storage_path(): void
    {
        $profile = JobSeekerProfile::factory()->create();

        $this->putFile($profile->cv_path);

        $this->get('/storage/'.$profile->cv_path)->assertForbidden();
    }

    public function test_a_job_seeker_downloads_their_own_cv(): void
    {
        $profile = JobSeekerProfile::factory()->create();

        $this->putFile($profile->cv_path);

        $this->actingAs($profile->user)
            ->get(route('job-seeker.cv.show'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_a_job_seeker_without_a_cv_gets_a_not_found(): void
    {
        $profile = JobSeekerProfile::factory()->withoutCv()->create();

        $this->actingAs($profile->user)
            ->get(route('job-seeker.cv.show'))
            ->assertNotFound();
    }

    public function test_the_company_that_posted_the_vacancy_downloads_an_applicant_cv(): void
    {
        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->published()->for($company)->create();
        $application = Application::factory()->for($vacancy, 'jobVacancy')->create();

        $this->putFile($application->cv_path);

        $this->actingAs($company->user)
            ->get(route('company.applicants.cv', $application))
            ->assertOk();
    }

    public function test_another_company_cannot_download_an_applicant_cv(): void
    {
        $application = Application::factory()->create();
        $intruder = Company::factory()->verified()->create();

        $this->putFile($application->cv_path);

        $this->actingAs($intruder->user)
            ->get(route('company.applicants.cv', $application))
            ->assertForbidden();
    }

    public function test_a_guest_cannot_download_an_applicant_cv(): void
    {
        $application = Application::factory()->create();

        $this->putFile($application->cv_path);

        $this->get(route('company.applicants.cv', $application))
            ->assertRedirect(route('login'));
    }

    public function test_a_company_and_an_admin_read_a_legality_document_but_outsiders_cannot(): void
    {
        $document = CompanyDocument::factory()->create();

        $this->putFile($document->file_path);

        $this->actingAs($document->company->user)
            ->get(route('company-documents.show', $document))
            ->assertOk();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('company-documents.show', $document))
            ->assertOk();

        $this->actingAs(Company::factory()->create()->user)
            ->get(route('company-documents.show', $document))
            ->assertForbidden();

        $this->actingAs(User::factory()->jobSeeker()->create())
            ->get(route('company-documents.show', $document))
            ->assertForbidden();
    }

    public function test_a_company_and_an_admin_read_a_payment_proof_but_outsiders_cannot(): void
    {
        $payment = Payment::factory()->waitingConfirmation()->create();

        $this->putFile($payment->proof_path);

        $this->actingAs($payment->company->user)
            ->get(route('payment-proofs.show', $payment))
            ->assertOk();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('payment-proofs.show', $payment))
            ->assertOk();

        $this->actingAs(Company::factory()->create()->user)
            ->get(route('payment-proofs.show', $payment))
            ->assertForbidden();

        $this->actingAs(User::factory()->jobSeeker()->create())
            ->get(route('payment-proofs.show', $payment))
            ->assertForbidden();
    }

    public function test_an_invoice_without_a_proof_yet_gives_a_not_found(): void
    {
        $payment = Payment::factory()->create();

        $this->actingAs($payment->company->user)
            ->get(route('payment-proofs.show', $payment))
            ->assertNotFound();
    }
}
