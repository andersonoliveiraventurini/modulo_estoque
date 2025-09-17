<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckUserBlocked
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->blocked) {
            Auth::logout();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Sua conta foi bloqueada pelo administrador.']);
        }

        return $next($request);
    }
}
