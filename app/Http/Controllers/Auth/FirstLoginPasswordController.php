<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class FirstLoginPasswordController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['contractor', 'student'], true), 403);

        if (! $request->user()->must_change_password) {
            return redirect()->route($request->user()->isStudent() ? 'student.portal' : 'contractor.dashboard');
        }

        return view('auth.first-login-password');
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['contractor', 'student'], true), 403);

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ])->save();

        return redirect()->route($request->user()->isStudent() ? 'student.portal' : 'contractor.dashboard')
            ->with('status', 'Your password has been updated.');
    }
}
