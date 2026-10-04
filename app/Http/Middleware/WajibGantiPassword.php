<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun dengan kata sandi awal/sementara (dibuat atau direset Admin) wajib menggantinya sebelum memakai aplikasi.
 */
class WajibGantiPassword
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password && ! $request->routeIs('password.ganti', 'password.ganti.update', 'logout')) {
            return redirect()->route('password.ganti');
        }

        return $next($request);
    }
}
