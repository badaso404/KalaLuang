<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobSeekerProfile;
use App\Models\JobVacancy;
use App\Models\User;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_seeker_with_a_complete_profile_can_apply(): void
    {
        $profile = JobSeekerProfile::factory()->create();
        $vacancy = JobVacancy::factory()->published()->create();

        $this->actingAs($profile->user)
            ->post(route('job-seeker.applications.store', $vacancy), ['cover_letter' => 'Saya tertarik.'])
            ->assertRedirect(route('job-seeker.applications.index'));

        $application = Application::sole();

        $this->assertSame($vacancy->id, $application->job_vacancy_id);
        $this->assertSame(ApplicationStatus::New, $application->status);
        $this->assertSame('Saya tertarik.', $application->cover_letter);
    }

    public function test_the_cv_is_snapshotted_onto_the_application(): void
    {
        $profile = JobSeekerProfile::factory()->create(['cv_path' => 'cv/versi-lama.pdf']);
        $vacancy = JobVacancy::factory()->published()->create();

        $this->actingAs($profile->user)->post(route('job-seeker.applications.store', $vacancy));

        $profile->update(['cv_path' => 'cv/versi-baru.pdf']);

        $this->assertSame('cv/versi-lama.pdf', Application::sole()->cv_path);
    }

    public function test_applying_requires_a_complete_profile(): void
    {
        $profile = JobSeekerProfile::factory()->incomplete()->create();
        $vacancy = JobVacancy::factory()->published()->create();

        $this->actingAs($profile->user)
            ->post(route('job-seeker.applications.store', $vacancy))
            ->assertRedirect(route('job-seeker.profile.edit'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_a_profile_without_a_cv_cannot_apply(): void
    {
        $profile = JobSeekerProfile::factory()->withoutCv()->create();
        $vacancy = JobVacancy::factory()->published()->create();

        $this->actingAs($profile->user)
            ->post(route('job-seeker.applications.store', $vacancy))
            ->assertRedirect(route('job-seeker.profile.edit'));

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_a_job_seeker_can_only_apply_once_per_vacancy(): void
    {
        $profile = JobSeekerProfile::factory()->create();
        $vacancy = JobVacancy::factory()->published()->create();

        $this->actingAs($profile->user)->post(route('job-seeker.applications.store', $vacancy));

        $this->actingAs($profile->user)
            ->post(route('job-seeker.applications.store', $vacancy))
            ->assertSessionHas('error', 'Anda sudah pernah melamar lowongan ini.');

        $this->assertDatabaseCount('applications', 1);
    }

    public function test_a_vacancy_outside_the_catalogue_cannot_be_applied_to(): void
    {
        $profile = JobSeekerProfile::factory()->create();
        $vacancy = JobVacancy::factory()->underReview()->create();

        $this->actingAs($profile->user)
            ->post(route('job-seeker.applications.store', $vacancy))
            ->assertNotFound();

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_an_unverified_job_seeker_is_sent_to_the_verification_notice(): void
    {
        $profile = JobSeekerProfile::factory()
            ->for(User::factory()->jobSeeker()->unverified())
            ->create();
        $vacancy = JobVacancy::factory()->published()->create();

        $this->actingAs($profile->user)
            ->post(route('job-seeker.applications.store', $vacancy))
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_the_owning_company_can_advance_an_applicant_and_the_applicant_is_notified(): void
    {
        Notification::fake();

        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->published()->for($company)->create();
        $application = Application::factory()->for($vacancy, 'jobVacancy')->create();

        $this->actingAs($company->user)
            ->put(route('company.applicants.status', $application), ['status' => 'accepted'])
            ->assertRedirect();

        $application->refresh();

        $this->assertSame(ApplicationStatus::Accepted, $application->status);
        $this->assertNotNull($application->status_updated_at);

        Notification::assertSentTo($application->user, ApplicationStatusChanged::class);
    }

    public function test_another_company_cannot_touch_an_applicant(): void
    {
        $application = Application::factory()->create();
        $intruder = Company::factory()->verified()->create();

        $this->actingAs($intruder->user)
            ->put(route('company.applicants.status', $application), ['status' => 'accepted'])
            ->assertForbidden();

        $this->assertSame(ApplicationStatus::New, $application->refresh()->status);
    }

    public function test_a_job_seeker_sees_their_application_status(): void
    {
        $profile = JobSeekerProfile::factory()->create();
        $vacancy = JobVacancy::factory()->published()->create(['title' => 'Staf Administrasi Junior']);
        Application::factory()->reviewed()->for($vacancy, 'jobVacancy')->for($profile->user)->create();

        $this->actingAs($profile->user)
            ->get(route('job-seeker.applications.index'))
            ->assertOk()
            ->assertSee('Staf Administrasi Junior')
            ->assertSee('Diproses');
    }
}
