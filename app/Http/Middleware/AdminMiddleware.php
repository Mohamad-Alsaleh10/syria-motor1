<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // أضف هذا

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return $next($request);
        }

        // إعادة التوجيه أو إظهار خطأ 403 (Unauthorized)
        return redirect()->route('dashboard')->with('error', 'ليس لديك صلاحية الوصول لهذه الصفحة.');
        // أو abort(403, 'Unauthorized action.');
    }
}
