<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActivePortalAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && in_array($user->role, ['contractor', 'student'], true) && $user->archived_at !== null) {
            Auth::guard('student')->logout();
            Auth::guard('contractor')->logout();
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')->with('status', 'This account has been deactivated. Contact your warden or contractor for assistance.');
        }

        return $next($request);
    }
}
