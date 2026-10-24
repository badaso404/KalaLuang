<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_forgot_password_page_is_reachable_from_the_login_page(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'), escape: false);

        $this->get(route('password.request'))->assertOk();
    }

    public function test_a_reset_link_is_mailed_to_a_registered_user(): void
    {
        Notification::fake();

        $user = User::factory()->jobSeeker()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    /**
     * The response must not reveal whether an email has an account here.
     */
    public function test_an_unknown_email_gets_the_same_confirmation_and_no_mail(): void
    {
        Notification::fake();

        $known = User::factory()->jobSeeker()->create();

        $forKnown = $this->post(route('password.email'), ['email' => $known->email]);
        $forUnknown = $this->post(route('password.email'), ['email' => 'bukan-siapa-siapa@example.com']);

        $forUnknown->assertSessionHasNoErrors();
        $this->assertSame(
            $forKnown->getSession()->get('status'),
            $forUnknown->getSession()->get('status'),
        );

        // Only the registered email produced a mail, so the identical replies
        // above cannot be used to tell the two apart.
        Notification::assertSentTo($known, ResetPassword::class);
        Notification::assertCount(1);
    }

    public function test_a_user_resets_their_password_with_the_mailed_token(): void
    {
        Notification::fake();

        $user = User::factory()->jobSeeker()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        $token = null;

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))->assertOk();

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password-baru-saya',
            'password_confirmation' => 'password-baru-saya',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('password-baru-saya', $user->refresh()->password));
        $this->assertGuest();
    }

    public function test_the_reset_mail_is_written_in_indonesian_and_links_to_the_reset_page(): void
    {
        $user = User::factory()->jobSeeker()->create();
        $notification = new ResetPassword('token-contoh');

        $mail = $notification->toMail($user);
        $rendered = $mail->render()->toHtml();

        $this->assertSame('Reset Password '.config('app.name'), $mail->subject);
        $this->assertStringContainsString('Kami menerima permintaan reset password', $rendered);
        $this->assertStringContainsString('Buat Password Baru', $rendered);
        $this->assertStringContainsString(
            route('password.reset', ['token' => 'token-contoh', 'email' => $user->email]),
            html_entity_decode($rendered),
        );
    }

    public function test_the_new_password_must_be_confirmed(): void
    {
        Notification::fake();

        $user = User::factory()->jobSeeker()->create();
        $originalPassword = $user->password;

        $this->post(route('password.email'), ['email' => $user->email]);

        $token = null;

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password-baru-saya',
            'password_confirmation' => 'beda-sama-sekali',
        ])->assertSessionHasErrors('password');

        $this->assertSame($originalPassword, $user->refresh()->password);
    }

    public function test_an_invalid_token_is_rejected(): void
    {
        $user = User::factory()->jobSeeker()->create();
        $originalPassword = $user->password;

        $this->post(route('password.store'), [
            'token' => 'token-karangan',
            'email' => $user->email,
            'password' => 'password-baru-saya',
            'password_confirmation' => 'password-baru-saya',
        ])->assertSessionHasErrors('email');

        $this->assertSame($originalPassword, $user->refresh()->password);
    }

    public function test_a_second_request_within_the_throttle_window_is_refused(): void
    {
        Notification::fake();

        $user = User::factory()->jobSeeker()->create();

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHasNoErrors();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHasErrors('email');

        Notification::assertCount(1);
    }

    public function test_a_signed_in_user_is_kept_away_from_the_reset_pages(): void
    {
        $user = User::factory()->jobSeeker()->create();

        $this->actingAs($user)->get(route('password.request'))->assertRedirect();
    }
}
