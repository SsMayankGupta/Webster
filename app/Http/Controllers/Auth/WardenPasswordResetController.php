<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WardenPasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.warden-forgot-password');
    }

    public function storeLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $warden = User::query()
            ->where('role', 'warden')
            ->where('email', $validated['email'])
            ->first();

        if ($warden !== null) {
            Password::broker()->sendResetLink(['email' => $warden->email]);
        }

        return back()->with(
            'status',
            'If a warden account exists for that email, a password reset link will be sent. If you recently requested one, wait before trying again.',
        );
    }

    public function createReset(Request $request): View
    {
        return view('auth.warden-reset-password', ['request' => $request]);
    }

    public function storeReset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $wardenExists = User::query()
            ->where('role', 'warden')
            ->where('email', $request->input('email'))
            ->exists();

        if (! $wardenExists) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __(Password::INVALID_TOKEN)]);
        }

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request): void {
                if (! $user->isWarden()) {
                    throw ValidationException::withMessages([
                        'email' => __(Password::INVALID_TOKEN),
                    ]);
                }

                $user->forceFill([
                    'password' => Hash::make($request->input('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('warden.login')->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
