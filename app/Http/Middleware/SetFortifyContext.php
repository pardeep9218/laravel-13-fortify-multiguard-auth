<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetFortifyContext
{
    public function handle(Request $request, Closure $next, string $guard)
    {
        config([
            'fortify.guard'     => $guard,
            'fortify.passwords' => $guard === 'admin' ? 'admins' : 'users',
            'fortify.home'      => $guard === 'admin' ? '/dashboard' : '/',
        ]);

        Auth::shouldUse($guard);
        app()->forgetInstance(StatefulGuard::class);

        return $next($request);
    }
}