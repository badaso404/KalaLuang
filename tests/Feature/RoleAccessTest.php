<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\JobSeekerProfile;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_private_areas(): void
    {
        foreach ([
            route('job-seeker.profile.edit'),
            route('job-seeker.applications.index'),
            route('job-seeker.saved.index'),
            route('company.dashboard'),
            route('admin.dashboard'),
        ] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_a_job_seeker_cannot_reach_company_or_admin_areas(): void
    {
        $seeker = User::factory()->jobSeeker()->create();

        $this->actingAs($seeker)->get(route('company.dashboard'))->assertForbidden();
        $this->actingAs($seeker)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($seeker)->get(route('admin.companies.index'))->assertForbidden();
    }

    public function test_a_company_cannot_reach_job_seeker_or_admin_areas(): void
    {
        $company = Company::factory()->verified()->create();

        $this->actingAs($company->user)->get(route('job-seeker.profile.edit'))->assertForbidden();
        $this->actingAs($company->user)->get(route('job-seeker.applications.index'))->assertForbidden();
        $this->actingAs($company->user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_an_admin_cannot_reach_job_seeker_or_company_areas(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('job-seeker.profile.edit'))->assertForbidden();
        $this->actingAs($admin)->get(route('company.dashboard'))->assertForbidden();
    }

    public function test_a_company_cannot_apply_or_save_a_vacancy(): void
    {
        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->published()->create();

        $this->actingAs($company->user)
            ->post(route('job-seeker.applications.store', $vacancy))
            ->assertForbidden();

        $this->actingAs($company->user)
            ->post(route('job-seeker.saved.store', $vacancy))
            ->assertForbidden();

        $this->assertDatabaseCount('applications', 0);
        $this->assertDatabaseCount('saved_vacancies', 0);
    }

    public function test_a_job_seeker_can_save_and_unsave_a_vacancy(): void
    {
        $profile = JobSeekerProfile::factory()->create();
        $vacancy = JobVacancy::factory()->published()->create();

        $this->actingAs($profile->user)
            ->post(route('job-seeker.saved.store', $vacancy))
            ->assertRedirect();

        $this->assertDatabaseCount('saved_vacancies', 1);

        // Saving twice must not create a duplicate row.
        $this->actingAs($profile->user)->post(route('job-seeker.saved.store', $vacancy));
        $this->assertDatabaseCount('saved_vacancies', 1);

        $this->actingAs($profile->user)
            ->delete(route('job-seeker.saved.destroy', $vacancy))
            ->assertRedirect();

        $this->assertDatabaseCount('saved_vacancies', 0);
    }

    public function test_job_seeker_pages_load(): void
    {
        $profile = JobSeekerProfile::factory()->create();

        foreach ([
            route('job-seeker.profile.edit'),
            route('job-seeker.applications.index'),
            route('job-seeker.saved.index'),
        ] as $url) {
            $this->actingAs($profile->user)->get($url)->assertOk();
        }
    }

    public function test_company_pages_load(): void
    {
        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->pendingPayment()->for($company)->create();

        foreach ([
            route('company.dashboard'),
            route('company.profile.edit'),
            route('company.verification.show'),
            route('company.vacancies.index'),
            route('company.vacancies.create'),
            route('company.vacancies.show', $vacancy),
            route('company.applicants.index'),
        ] as $url) {
            $this->actingAs($company->user)->get($url)->assertOk();
        }
    }

    public function test_a_company_without_a_profile_is_sent_to_fill_it_in(): void
    {
        $user = User::factory()->company()->create();

        $this->actingAs($user)
            ->get(route('company.dashboard'))
            ->assertRedirect(route('company.profile.edit'));

        $this->actingAs($user)->get(route('company.profile.edit'))->assertOk();
    }
}
