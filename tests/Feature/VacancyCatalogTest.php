<?php

namespace Tests\Feature;

use App\Enums\EmploymentType;
use App\Models\Category;
use App\Models\Company;
use App\Models\JobVacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_browse_the_catalogue(): void
    {
        $vacancy = JobVacancy::factory()->published()->create(['title' => 'Staf Administrasi Junior']);

        $this->get(route('vacancies.index'))
            ->assertOk()
            ->assertSee('Staf Administrasi Junior')
            ->assertSee('Tanpa pengalaman');

        $this->get(route('vacancies.show', $vacancy))
            ->assertOk()
            ->assertSee('Staf Administrasi Junior');
    }

    public function test_every_catalogue_card_links_to_its_detail_page(): void
    {
        $vacancy = JobVacancy::factory()->published()->create();

        $this->get(route('vacancies.index'))
            ->assertOk()
            ->assertSee(route('vacancies.show', $vacancy));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('vacancies.show', $vacancy));
    }

    public function test_the_detail_page_needs_no_login_and_offers_guests_a_way_in(): void
    {
        $vacancy = JobVacancy::factory()->published()->create();

        $this->get(route('vacancies.show', $vacancy))
            ->assertOk()
            ->assertSee('Masuk untuk Melamar')
            ->assertSee('Deskripsi pekerjaan')
            ->assertSee('Persyaratan');
    }

    public function test_catalogue_only_lists_active_vacancies_from_verified_companies(): void
    {
        $verified = Company::factory()->verified()->create();

        $listed = JobVacancy::factory()->published()->for($verified)->create(['title' => 'Tayang Sekarang']);
        $underReview = JobVacancy::factory()->underReview()->for($verified)->create(['title' => 'Belum Dikurasi']);
        $overdue = JobVacancy::factory()->overdue()->for($verified)->create(['title' => 'Sudah Lewat Tanggal']);
        $unverifiedCompany = JobVacancy::factory()->published()
            ->for(Company::factory()->create())
            ->create(['title' => 'Perusahaan Belum Terverifikasi']);
        $suspendedCompany = JobVacancy::factory()->published()
            ->for(Company::factory()->suspended()->create())
            ->create(['title' => 'Perusahaan Ditangguhkan']);

        $response = $this->get(route('vacancies.index'))->assertOk();

        $response->assertSee('Tayang Sekarang');
        $response->assertDontSee('Belum Dikurasi');
        $response->assertDontSee('Sudah Lewat Tanggal');
        $response->assertDontSee('Perusahaan Belum Terverifikasi');
        $response->assertDontSee('Perusahaan Ditangguhkan');

        $this->assertEquals([$listed->id], JobVacancy::published()->pluck('id')->all());

        foreach ([$underReview, $overdue, $unverifiedCompany, $suspendedCompany] as $hidden) {
            $this->get(route('vacancies.show', $hidden))->assertNotFound();
        }
    }

    public function test_catalogue_filters_by_employment_type_category_and_location(): void
    {
        $marketing = Category::factory()->create(['name' => 'Pemasaran', 'slug' => 'pemasaran']);
        $finance = Category::factory()->create(['name' => 'Keuangan', 'slug' => 'keuangan']);

        JobVacancy::factory()->published()->for($marketing)->create([
            'title' => 'Marketing Trainee',
            'employment_type' => EmploymentType::Internship,
            'location' => 'Bandung',
        ]);
        JobVacancy::factory()->published()->for($finance)->create([
            'title' => 'Asisten Keuangan',
            'employment_type' => EmploymentType::FullTime,
            'location' => 'Surabaya',
        ]);
        JobVacancy::factory()->published()->remote()->for($marketing)->create([
            'title' => 'Social Media Remote',
            'employment_type' => EmploymentType::PartTime,
        ]);

        $this->get(route('vacancies.index', ['employment_type' => 'internship']))
            ->assertOk()
            ->assertSee('Marketing Trainee')
            ->assertDontSee('Asisten Keuangan');

        $this->get(route('vacancies.index', ['category' => 'keuangan']))
            ->assertOk()
            ->assertSee('Asisten Keuangan')
            ->assertDontSee('Marketing Trainee');

        $this->get(route('vacancies.index', ['location' => 'Surabaya']))
            ->assertOk()
            ->assertSee('Asisten Keuangan')
            ->assertDontSee('Marketing Trainee');

        $this->get(route('vacancies.index', ['location' => 'remote']))
            ->assertOk()
            ->assertSee('Social Media Remote')
            ->assertDontSee('Asisten Keuangan');

        $this->get(route('vacancies.index', ['q' => 'Asisten']))
            ->assertOk()
            ->assertSee('Asisten Keuangan')
            ->assertDontSee('Marketing Trainee');
    }

    public function test_home_page_shows_published_vacancies_and_categories(): void
    {
        $category = Category::factory()->create(['name' => 'Desain', 'slug' => 'desain']);
        JobVacancy::factory()->published()->for($category)->create(['title' => 'Junior Designer']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Junior Designer')
            ->assertSee('Desain')
            ->assertSee('1 lowongan tayang');
    }
}
