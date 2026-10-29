<?php

namespace Tests\Feature;

use App\Enums\EducationLevel;
use App\Models\Application;
use App\Models\Category;
use App\Models\Company;
use App\Models\JobSeekerProfile;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_the_public_landing_page(): void
    {
        $category = Category::factory()->create(['name' => 'Pemasaran', 'slug' => 'pemasaran']);
        JobVacancy::factory()->published()->for($category)->create(['title' => 'Digital Marketing Trainee']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Digital Marketing Trainee')
            ->assertSee('Pemasaran')
            ->assertSee('Saringan tiga lapis sebelum sebuah lowongan sampai ke Anda')
            ->assertSee('Janji KalaLuang')
            ->assertSee('Daftar sebagai Perusahaan');
    }

    public function test_the_landing_page_renders_every_promo_slide(): void
    {
        $response = $this->get(route('home'))->assertOk();

        foreach (config('kalaluang.promos') as $promo) {
            $response->assertSee($promo['title'], escape: false);
            $response->assertSee($promo['cta']['label']);
        }
    }

    public function test_the_landing_page_lists_all_job_fields_even_without_vacancies(): void
    {
        Category::factory()->create(['name' => 'Kesehatan', 'slug' => 'kesehatan']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kesehatan')
            ->assertSee('Menunggu lowongan baru');
    }

    public function test_job_seekers_see_a_personal_feed_instead_of_the_landing_page(): void
    {
        $user = User::factory()->jobSeeker()->create(['name' => 'Rina Pratiwi']);
        JobSeekerProfile::factory()->for($user)->create([
            'city' => 'Bandung',
            'education_level' => EducationLevel::D3,
        ]);

        $matching = JobVacancy::factory()->published()->create([
            'title' => 'Admin Junior Bandung',
            'location' => 'Bandung',
            'min_education' => EducationLevel::D3,
        ]);

        Application::factory()->for($user)->for($matching)->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Halo, Rina Pratiwi')
            ->assertSee('Kesiapan profil')
            ->assertSee('Lamaran terakhir')
            ->assertSee('Admin Junior Bandung')
            ->assertDontSee('Saringan tiga lapis sebelum sebuah lowongan sampai ke Anda');
    }

    public function test_the_personal_feed_hides_vacancies_the_job_seeker_already_applied_to(): void
    {
        $user = User::factory()->jobSeeker()->create();
        JobSeekerProfile::factory()->for($user)->create(['education_level' => EducationLevel::S1]);

        $applied = JobVacancy::factory()->published()->create([
            'title' => 'Sudah Dilamar',
            'min_education' => EducationLevel::S1,
        ]);
        JobVacancy::factory()->published()->create([
            'title' => 'Belum Dilamar',
            'min_education' => EducationLevel::S1,
        ]);

        Application::factory()->for($user)->for($applied)->create();

        $response = $this->actingAs($user)->get(route('home'))->assertOk();

        $response->assertSee('Belum Dilamar');

        // "Sudah Dilamar" hanya boleh muncul satu kali, yaitu pada daftar
        // "Lamaran terakhir" — bukan sebagai rekomendasi baru.
        $this->assertSame(1, substr_count($response->getContent(), route('vacancies.show', $applied)));
    }

    public function test_companies_see_their_own_shortcut_feed(): void
    {
        $user = User::factory()->company()->create();
        Company::factory()->verified()->for($user)->create(['name' => 'PT Contoh Maju']);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('PT Contoh Maju')
            ->assertSee('Pelamar baru')
            ->assertSee('Pasang Lowongan')
            ->assertDontSee('Janji KalaLuang');
    }

    public function test_admins_see_the_moderation_queues_on_the_home_page(): void
    {
        $admin = User::factory()->admin()->create();
        Company::factory()->create(); // masih menunggu verifikasi

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Verifikasi perusahaan')
            ->assertSee('Buka Panel Admin')
            ->assertSee('Pengingat saringan tiga lapis');
    }
}
