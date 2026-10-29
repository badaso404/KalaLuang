<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\VacancyStatus;
use App\Models\Application;
use App\Models\Category;
use App\Models\Company;
use App\Models\JobSeekerProfile;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_history_summarises_progress_across_all_applications(): void
    {
        $user = User::factory()->jobSeeker()->create();

        Application::factory()->count(2)->for($user)->create();
        Application::factory()->reviewed()->for($user)->create();
        Application::factory()->for($user)->create(['status' => ApplicationStatus::Accepted]);

        $this->actingAs($user)
            ->get(route('job-seeker.applications.index'))
            ->assertOk()
            ->assertSee('Total lamaran')
            ->assertSee('Tingkat respons')
            ->assertSee('50%'); // 2 dari 4 lamaran sudah ditanggapi
    }

    public function test_applications_can_be_filtered_by_status(): void
    {
        $user = User::factory()->jobSeeker()->create();

        Application::factory()->for($user)->for(
            JobVacancy::factory()->published()->create(['title' => 'Masih Baru']), 'jobVacancy',
        )->create();

        Application::factory()->reviewed()->for($user)->for(
            JobVacancy::factory()->published()->create(['title' => 'Sudah Diproses']), 'jobVacancy',
        )->create();

        $this->actingAs($user)
            ->get(route('job-seeker.applications.index', ['status' => 'reviewed']))
            ->assertOk()
            ->assertSee('Sudah Diproses')
            ->assertDontSee('Masih Baru');
    }

    public function test_applications_can_be_searched_by_position_or_company(): void
    {
        $user = User::factory()->jobSeeker()->create();

        Application::factory()->for($user)->for(
            JobVacancy::factory()->published()
                ->for(Company::factory()->verified()->create(['name' => 'PT Contoh Maju']))
                ->create(['title' => 'Staf Administrasi']),
            'jobVacancy',
        )->create();

        Application::factory()->for($user)->for(
            JobVacancy::factory()->published()
                ->for(Company::factory()->verified()->create(['name' => 'CV Contoh Kreatif']))
                ->create(['title' => 'Desainer Grafis']),
            'jobVacancy',
        )->create();

        $this->actingAs($user)
            ->get(route('job-seeker.applications.index', ['q' => 'Administrasi']))
            ->assertOk()
            ->assertSee('Staf Administrasi')
            ->assertDontSee('Desainer Grafis');

        $this->actingAs($user)
            ->get(route('job-seeker.applications.index', ['q' => 'Kreatif']))
            ->assertOk()
            ->assertSee('Desainer Grafis')
            ->assertDontSee('Staf Administrasi');
    }

    public function test_applications_can_be_sorted_oldest_first(): void
    {
        $user = User::factory()->jobSeeker()->create();

        Application::factory()->for($user)->for(
            JobVacancy::factory()->published()->create(['title' => 'Lamaran Lama']), 'jobVacancy',
        )->create(['created_at' => now()->subMonth()]);

        Application::factory()->for($user)->for(
            JobVacancy::factory()->published()->create(['title' => 'Lamaran Baru']), 'jobVacancy',
        )->create(['created_at' => now()]);

        $this->actingAs($user)
            ->get(route('job-seeker.applications.index', ['sort' => 'terlama']))
            ->assertOk()
            ->assertSeeInOrder(['Lamaran Lama', 'Lamaran Baru']);

        $this->actingAs($user)
            ->get(route('job-seeker.applications.index'))
            ->assertOk()
            ->assertSeeInOrder(['Lamaran Baru', 'Lamaran Lama']);
    }

    public function test_an_invalid_sort_option_is_rejected(): void
    {
        $user = User::factory()->jobSeeker()->create();

        $this->actingAs($user)
            ->get(route('job-seeker.applications.index', ['sort' => 'acak']))
            ->assertSessionHasErrors('sort');
    }

    public function test_the_history_warns_when_the_vacancy_is_no_longer_listed(): void
    {
        $user = User::factory()->jobSeeker()->create();
        $vacancy = JobVacancy::factory()->published()->create([
            'title' => 'Lowongan Bermasalah',
            'status' => VacancyStatus::TakenDown,
        ]);

        Application::factory()->for($user)->for($vacancy, 'jobVacancy')->create();

        $response = $this->actingAs($user)
            ->get(route('job-seeker.applications.index'))
            ->assertOk()
            ->assertSee('Lowongan Bermasalah')
            ->assertSee('sudah diturunkan admin setelah laporan pengguna terbukti.');

        // Lowongan yang turun tidak lagi punya halaman publik, jadi jangan ditautkan.
        $response->assertDontSee(route('vacancies.show', $vacancy));
    }

    public function test_a_rejected_application_points_to_other_vacancies_in_the_same_field(): void
    {
        $user = User::factory()->jobSeeker()->create();
        $category = Category::factory()->create(['name' => 'Desain', 'slug' => 'desain']);

        Application::factory()->for($user)->for(
            JobVacancy::factory()->published()->for($category)->create(), 'jobVacancy',
        )->create(['status' => ApplicationStatus::Rejected]);

        $this->actingAs($user)
            ->get(route('job-seeker.applications.index'))
            ->assertOk()
            ->assertSee('Cari lowongan Desain lainnya')
            ->assertSee(route('vacancies.index', ['category' => 'desain']));
    }

    public function test_status_updates_reach_the_job_seeker_as_notifications(): void
    {
        $profile = JobSeekerProfile::factory()->create();
        $user = $profile->user;

        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->published()->for($company)->create(['title' => 'Asisten Keuangan']);
        $application = Application::factory()->for($user)->for($vacancy, 'jobVacancy')->create();

        $this->actingAs($company->user)
            ->put(route('company.applicants.status', $application), ['status' => 'reviewed'])
            ->assertRedirect();

        $this->assertSame(1, $user->unreadNotifications()->count());

        // Lencana jumlah pembaruan tampil di navigasi sebelum riwayat dibuka.
        $this->actingAs($user)
            ->get(route('vacancies.index'))
            ->assertOk()
            ->assertSee('pembaruan belum dibaca');

        $this->actingAs($user)
            ->get(route('job-seeker.applications.index'))
            ->assertOk()
            ->assertSee('Pembaruan terbaru')
            ->assertSee('Status lamaran Anda untuk &quot;Asisten Keuangan&quot; kini: Diproses.', escape: false);

        // Membuka riwayat menandai pembaruan sebagai sudah dibaca.
        $this->assertSame(0, $user->refresh()->unreadNotifications()->count());

        $this->actingAs($user)
            ->get(route('vacancies.index'))
            ->assertOk()
            ->assertDontSee('pembaruan belum dibaca');
    }

    public function test_a_job_seeker_only_sees_their_own_applications(): void
    {
        $user = User::factory()->jobSeeker()->create();
        $stranger = User::factory()->jobSeeker()->create();

        Application::factory()->for($stranger)->for(
            JobVacancy::factory()->published()->create(['title' => 'Lamaran Orang Lain']), 'jobVacancy',
        )->create();

        $this->actingAs($user)
            ->get(route('job-seeker.applications.index'))
            ->assertOk()
            ->assertSee('Anda belum mengirim lamaran apa pun.')
            ->assertDontSee('Lamaran Orang Lain');
    }

    public function test_a_company_can_attach_a_follow_up_note_that_reaches_the_applicant(): void
    {
        $user = User::factory()->jobSeeker()->create();
        $company = Company::factory()->verified()->create();
        $vacancy = JobVacancy::factory()->published()->for($company)->create();
        $application = Application::factory()->for($user)->for($vacancy, 'jobVacancy')->create();

        $note = 'Selamat! Tim kami menghubungi Anda lewat email hari Senin untuk jadwal wawancara.';

        $this->actingAs($company->user)
            ->put(route('company.applicants.status', $application), [
                'status' => 'accepted',
                'status_note' => $note,
            ])
            ->assertRedirect();

        $this->assertSame($note, $application->refresh()->status_note);
        $this->assertSame($note, $user->notifications()->sole()->data['note']);

        $this->actingAs($user)
            ->get(route('job-seeker.applications.index'))
            ->assertOk()
            ->assertSee('Catatan dari '.$company->name)
            ->assertSee($note);
    }

    public function test_an_accepted_application_reveals_how_to_reach_the_company(): void
    {
        $user = User::factory()->jobSeeker()->create();
        $pic = User::factory()->company()->create(['name' => 'Budi Santoso', 'email' => 'hr@contohmaju.test']);
        $company = Company::factory()->verified()->for($pic)->create([
            'name' => 'PT Contoh Maju',
            'address' => 'Jl. Gatot Subroto No. 12, Jakarta Selatan',
        ]);

        Application::factory()->for($user)->for(
            JobVacancy::factory()->published()->for($company)->create(), 'jobVacancy',
        )->create(['status' => ApplicationStatus::Accepted]);

        $this->actingAs($user)
            ->get(route('job-seeker.applications.index'))
            ->assertOk()
            ->assertSee('ini cara menghubungi mereka', escape: false)
            ->assertSee('Budi Santoso')
            ->assertSee('hr@contohmaju.test')
            ->assertSee('Jl. Gatot Subroto No. 12, Jakarta Selatan');
    }

    public function test_the_company_contact_stays_hidden_before_the_applicant_is_accepted(): void
    {
        $user = User::factory()->jobSeeker()->create();
        $pic = User::factory()->company()->create(['email' => 'rahasia@contohmaju.test']);
        $company = Company::factory()->verified()->for($pic)->create();

        Application::factory()->reviewed()->for($user)->for(
            JobVacancy::factory()->published()->for($company)->create(), 'jobVacancy',
        )->create();

        $this->actingAs($user)
            ->get(route('job-seeker.applications.index'))
            ->assertOk()
            ->assertDontSee('rahasia@contohmaju.test')
            ->assertDontSee('ini cara menghubungi mereka', escape: false);
    }

    public function test_a_follow_up_note_longer_than_the_limit_is_rejected(): void
    {
        $company = Company::factory()->verified()->create();
        $application = Application::factory()->for(
            JobVacancy::factory()->published()->for($company)->create(), 'jobVacancy',
        )->create();

        $this->actingAs($company->user)
            ->put(route('company.applicants.status', $application), [
                'status' => 'accepted',
                'status_note' => str_repeat('a', 1001),
            ])
            ->assertSessionHasErrors('status_note');

        $this->assertSame(ApplicationStatus::New, $application->refresh()->status);
    }
}
