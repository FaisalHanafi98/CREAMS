<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeadersMiddleware
 *
 * Adds security headers to HTTP responses to protect against common web vulnerabilities.
 *
 * Headers added:
 * - X-Frame-Options: Prevents clickjacking attacks
 * - X-Content-Type-Options: Prevents MIME sniffing
 * - X-XSS-Protection: Enables browser XSS protection
 * - Referrer-Policy: Controls referrer information leakage
 * - Content-Security-Policy: Prevents XSS and injection attacks
 * - Strict-Transport-Security: Enforces HTTPS (production only)
 *
 * @see https://owasp.org/www-project-secure-headers/
 * @see PRODUCTION_DEPLOYMENT.md for deployment configuration
 */
class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and add security headers to the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // X-Frame-Options: Prevent clickjacking by disallowing framing from other domains
        // SAMEORIGIN allows framing only from the same origin
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // X-Content-Type-Options: Prevent MIME-type sniffing
        // Ensures browsers respect the Content-Type header
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-XSS-Protection: Enable browser's built-in XSS protection
        // mode=block tells browser to block the page if XSS is detected
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy: Control how much referrer information is included
        // strict-origin-when-cross-origin sends origin for cross-origin, full URL for same-origin
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content-Security-Policy: Prevent XSS and data injection attacks
        // This is a permissive policy for Laravel apps with inline scripts
        // TODO: Tighten this policy as the application evolves (remove 'unsafe-inline' if possible)
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'", // Laravel Mix/Vite requires unsafe-inline
            "style-src 'self' 'unsafe-inline'",                 // Tailwind requires unsafe-inline
            "img-src 'self' data: https:",
            "font-src 'self' data:",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ];
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        // Strict-Transport-Security (HSTS): Enforce HTTPS
        // Only add in production when HTTPS is properly configured
        // max-age=31536000 = 1 year, includeSubDomains applies to all subdomains
        if (app()->environment('production') && $request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // Permissions-Policy: Control which browser features can be used
        // Restricts access to sensitive APIs like camera, microphone, geolocation
        $permissionsPolicy = [
            'camera=()',
            'microphone=()',
            'geolocation=(self)',
            'payment=()',
        ];
        $response->headers->set('Permissions-Policy', implode(', ', $permissionsPolicy));

        return $response;
    }
}
