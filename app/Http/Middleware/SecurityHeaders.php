<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldForceHttps($request)) {
            return $this->redirectToHttps($request);
        }

        $response = $next($request);

        $response->headers->remove('X-Powered-By');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', "accelerometer=(), camera=(), geolocation=(), gyroscope=(), microphone=(), payment=(), usb=()");
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());

        if ($this->isSecureRequest($request)) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload',
            );
        }

        return $response;
    }

    protected function shouldForceHttps(Request $request): bool
    {
        return (bool) config('app.force_https', false) && ! $this->isSecureRequest($request);
    }

    protected function isSecureRequest(Request $request): bool
    {
        return $request->isSecure() || strtolower((string) $request->header('X-Forwarded-Proto')) === 'https';
    }

    protected function redirectToHttps(Request $request): RedirectResponse
    {
        $httpsUrl = preg_replace('/^http:/i', 'https:', $request->fullUrl()) ?: $request->fullUrl();

        return redirect()->to($httpsUrl, 301);
    }

    protected function contentSecurityPolicy(): string
    {
        $scriptSrc = app()->isLocal()
            ? "script-src 'self' 'unsafe-inline' 'unsafe-eval'"
            : "script-src 'self' 'unsafe-inline'";

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "img-src 'self' data: blob:",
            "font-src 'self' https://fonts.bunny.net data:",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            $scriptSrc,
            "connect-src 'self'",
        ];

        return implode('; ', $directives);
    }
}
