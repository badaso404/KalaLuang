<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.register', [
            'selectedRole' => $request->query('peran') === UserRole::Company->value
                ? UserRole::Company
                : UserRole::JobSeeker,
        ]);
    }

    /**
     * Register a job seeker or a company. Admins are seeded, never registered.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => [
                'required',
                Rule::enum(UserRole::class)->only([UserRole::JobSeeker, UserRole::Company]),
            ],
        ], [], [
            'name' => 'nama',
            'email' => 'email',
            'password' => 'password',
            'role' => 'peran',
        ]);

        $user = User::create($validated);

        if ($user->isJobSeeker()) {
            $user->jobSeekerProfile()->create();
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
