<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    protected function payload(array $overrides = []): array
    {
        return [
            'name' => 'Rina Pratiwi',
            'email' => 'rina@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'job_seeker',
            ...$overrides,
        ];
    }

    public function test_registering_as_a_job_seeker_creates_an_empty_profile(): void
    {
        Notification::fake();

        $this->post(route('register'), $this->payload())
            ->assertRedirect(route('verification.notice'));

        $user = User::sole();

        $this->assertSame(UserRole::JobSeeker, $user->role);
        $this->assertNotNull($user->jobSeekerProfile);
        $this->assertFalse($user->jobSeekerProfile->isComplete());
        $this->assertAuthenticatedAs($user);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registering_as_a_company_does_not_create_a_job_seeker_profile(): void
    {
        Notification::fake();

        $this->post(route('register'), $this->payload(['role' => 'company']));

        $user = User::sole();

        $this->assertSame(UserRole::Company, $user->role);
        $this->assertNull($user->jobSeekerProfile);
        $this->assertNull($user->company);
    }

    public function test_nobody_can_register_as_an_admin(): void
    {
        $this->post(route('register'), $this->payload(['role' => 'admin']))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_the_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'rina@example.test']);

        $this->post(route('register'), $this->payload())
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_the_password_must_be_confirmed(): void
    {
        $this->post(route('register'), $this->payload(['password_confirmation' => 'beda']))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_a_user_can_log_in_and_is_sent_to_their_own_landing_page(): void
    {
        $seeker = User::factory()->jobSeeker()->create(['email' => 'seeker@example.test']);

        $this->post(route('login'), ['email' => 'seeker@example.test', 'password' => 'password'])
            ->assertRedirect(route('vacancies.index'));

        $this->assertAuthenticatedAs($seeker);
    }

    public function test_an_admin_lands_on_the_admin_panel(): void
    {
        User::factory()->admin()->create(['email' => 'admin@example.test']);

        $this->post(route('login'), ['email' => 'admin@example.test', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_a_company_lands_on_its_dashboard(): void
    {
        User::factory()->company()->create(['email' => 'hr@example.test']);

        $this->post(route('login'), ['email' => 'hr@example.test', 'password' => 'password'])
            ->assertRedirect(route('company.dashboard'));
    }

    public function test_bad_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'seeker@example.test']);

        $this->post(route('login'), ['email' => 'seeker@example.test', 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
