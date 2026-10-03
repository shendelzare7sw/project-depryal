<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan dasar untuk semua respons web.
 * CSP tidak dipasang karena Tailwind Play CDN & Alpine memerlukan skrip inline/eval (lihat DECISIONS).
 */
class SecurityHeaders
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');

        if ($request->user()) {
            // Halaman berisi data internal tidak boleh disimpan cache browser/proxy (mis. tombol Back setelah logout).
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }
}
