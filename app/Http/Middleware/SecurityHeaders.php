<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan untuk semua respons web, termasuk Content-Security-Policy:
 * semua aset di-host sendiri, skrip inline hanya dengan nonce, pihak ketiga hanya Cloudflare Turnstile.
 * 'unsafe-eval' diperlukan Alpine.js (ekspresi x-data/@click dievaluasi saat runtime);
 * style 'unsafe-inline' diperlukan atribut style dinamis (lebar bar persen) & SweetAlert2.
 */
class SecurityHeaders
{
    private const TURNSTILE = 'https://challenges.cloudflare.com';

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Vite::useCspNonce();
        $response = $next($request);

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval' ".self::TURNSTILE,
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob:",
            "font-src 'self'",
            "connect-src 'self' ".self::TURNSTILE,
            'frame-src '.self::TURNSTILE,
            "form-action 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "object-src 'none'",
        ]));
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
