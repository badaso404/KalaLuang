<?php

namespace Tests\Feature;

use App\Enums\DeviceType;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitStatisticTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_public_page_view_is_recorded(): void
    {
        $this->withServerVariables(['HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)'])
            ->get(route('vacancies.index'))
            ->assertOk();

        $visit = Visit::query()->sole();

        $this->assertSame('/lowongan', $visit->path);
        $this->assertSame('vacancies.index', $visit->route_name);
        $this->assertSame(DeviceType::Mobile, $visit->device);
        $this->assertNull($visit->user_id);
        $this->assertNull($visit->referrer_host);
        $this->assertSame(64, mb_strlen($visit->visitor_hash));
    }

    public function test_the_visit_records_the_logged_in_user_and_the_referring_site(): void
    {
        $user = User::factory()->create(['role' => UserRole::JobSeeker]);

        $this->actingAs($user)
            ->get(route('vacancies.index'), ['referer' => 'https://www.google.com/search?q=kalaluang'])
            ->assertOk();

        $visit = Visit::query()->sole();

        $this->assertSame($user->id, $visit->user_id);
        $this->assertSame('www.google.com', $visit->referrer_host);
    }

    public function test_navigation_inside_the_site_is_not_counted_as_a_referrer(): void
    {
        $this->get(route('vacancies.index'), ['referer' => route('home')])->assertOk();

        $this->assertNull(Visit::query()->sole()->referrer_host);
    }

    public function test_crawlers_are_not_counted(): void
    {
        $this->withServerVariables(['HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1)'])
            ->get(route('vacancies.index'))
            ->assertOk();

        $this->assertSame(0, Visit::query()->count());
    }

    public function test_the_admin_panel_and_its_admins_are_not_counted(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('vacancies.index'))->assertOk();

        $this->assertSame(0, Visit::query()->count());
    }

    public function test_pages_that_do_not_return_a_successful_html_response_are_not_counted(): void
    {
        $this->get('/halaman-yang-tidak-ada')->assertNotFound();
        $this->post(route('login'), ['email' => 'salah@contoh.test', 'password' => 'salah'])
            ->assertRedirect();

        $this->assertSame(0, Visit::query()->count());
    }

    public function test_an_admin_sees_the_visit_statistics(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $visitor = hash('sha256', 'pengunjung-tetap');

        Visit::factory()->count(3)->today()->fromVisitor($visitor)->on('/lowongan')->create();
        Visit::factory()->today()->on('/')->create([
            'referrer_host' => 'www.google.com',
            'device' => DeviceType::Desktop,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.visits.index'))
            ->assertOk()
            ->assertSee('Kunjungan hari ini')
            ->assertSee('4')
            ->assertSee('2 pengunjung unik')
            ->assertSee('/lowongan')
            ->assertSee('www.google.com')
            ->assertSee('Desktop');
    }

    public function test_only_admins_may_see_the_visit_statistics(): void
    {
        $this->get(route('admin.visits.index'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['role' => UserRole::JobSeeker]))
            ->get(route('admin.visits.index'))
            ->assertForbidden();
    }

    public function test_visits_older_than_the_retention_window_are_pruned(): void
    {
        config(['kalaluang.visit_retention_days' => 90]);

        $fresh = Visit::factory()->today()->create();
        $stale = Visit::factory()->create(['created_at' => now()->subDays(91)]);

        $this->artisan('model:prune', ['--model' => [Visit::class]])->assertSuccessful();

        $this->assertModelExists($fresh);
        $this->assertModelMissing($stale);
    }
}
