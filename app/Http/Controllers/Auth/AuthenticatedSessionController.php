<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::guard('web')->user();
        if (in_array($user?->role, ['contractor', 'warden'], true)) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'Use your role-specific portal to sign in.',
            ]);
        }

        if ($user?->role === 'student' && $user->archived_at !== null) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'This account has been deactivated. Contact your contractor for assistance.',
            ]);
        }

        $request->session()->regenerate();

        if ($user?->role === 'student' && $user->must_change_password) {
            return redirect()->route('password.first-login');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
