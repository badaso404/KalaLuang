<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configurePasswordResetMail();
    }

    /**
     * Throttle sign-in attempts per email and IP address together, so one
     * attacker cannot lock a victim out by hammering their email alone.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(Str::transliterate(
                Str::lower($request->string('email')).'|'.$request->ip(),
            ));
        });
    }

    /**
     * Laravel's stock reset mail is written in English, which does not fit an
     * Indonesian audience, so the body is composed here instead.
     */
    protected function configurePasswordResetMail(): void
    {
        ResetPassword::toMailUsing(function (User $user, string $token) {
            $minutes = config('auth.passwords.users.expire');

            return (new MailMessage)
                ->subject('Reset Password '.config('app.name'))
                ->greeting('Halo, '.$user->name)
                ->line('Kami menerima permintaan reset password untuk akun Anda.')
                ->action('Buat Password Baru', route('password.reset', [
                    'token' => $token,
                    'email' => $user->getEmailForPasswordReset(),
                ]))
                ->line('Tautan ini berlaku '.$minutes.' menit.')
                ->line('Jika Anda tidak meminta reset password, abaikan email ini. Password Anda tidak akan berubah.')
                ->salutation('Terima kasih, Tim '.config('app.name'));
        });
    }
}
