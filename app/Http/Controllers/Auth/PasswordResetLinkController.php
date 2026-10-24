<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Mail a reset link. An unknown email gets the same confirmation as a known
     * one, so the form cannot be used to find out who has an account here.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [], ['email' => 'email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_THROTTLED) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Tautan reset baru saja dikirim. Tunggu satu menit sebelum meminta lagi.',
                ]);
        }

        return back()->with(
            'status',
            'Jika email tersebut terdaftar, kami telah mengirimkan tautan reset password ke sana. Tautan berlaku 60 menit.',
        );
    }
}
