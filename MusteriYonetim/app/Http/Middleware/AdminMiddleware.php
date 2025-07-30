<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        // Giriş yapmış ama admin değilse (örneğin guest ise)
        if (Auth::check() && Auth::user()->role !== 'admin') {
            return redirect()->route('unauthorized'); // özel sayfaya yönlendir
        }

        return $next($request);
    }
}
