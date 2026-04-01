<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return redirect('login');
        }

        $userRole = $request->user()->role;

        // Cek apakah role user ada di daftar role yang diizinkan
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        return abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
