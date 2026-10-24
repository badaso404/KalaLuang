<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('login');
    }

    public function test_repeated_failed_sign_in_attempts_are_blocked(): void
    {
        $user = User::factory()->jobSeeker()->create();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login'), [
                'email' => $user->email,
                'password' => 'password-salah',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password-salah',
        ])->assertTooManyRequests();
    }

    public function test_the_limit_is_scoped_to_the_email_so_other_accounts_still_sign_in(): void
    {
        $victim = User::factory()->jobSeeker()->create();
        $other = User::factory()->jobSeeker()->create();

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post(route('login'), [
                'email' => $victim->email,
                'password' => 'password-salah',
            ]);
        }

        $this->post(route('login'), [
            'email' => $other->email,
            'password' => 'password',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($other);
    }
}
