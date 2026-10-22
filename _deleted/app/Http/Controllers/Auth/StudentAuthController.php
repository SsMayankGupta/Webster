<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentAuthController extends Controller
{
    public function create(): View
    {
        return view('auth.student-login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'college_id' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $user = User::where('role', 'student')
            ->where(function ($q) use ($credentials) {
                $q->where('college_id', $credentials['college_id'])
                  ->orWhere('email', $credentials['college_id']);
            })->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::guard('student')->login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->intended(route('student.dashboard'));
        }

        return back()->withErrors(['college_id' => 'Invalid credentials.'])->onlyInput('college_id');
    }

    public function destroy(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
