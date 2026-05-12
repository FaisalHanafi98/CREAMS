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
        // CDN domains allowlisted for Bootstrap, FontAwesome, jQuery, and Google Fonts.
        // cdn.jsdelivr.net also needed in connect-src: self-hosted Bootstrap minified files
        // contain sourceMappingURL comments that point back to the CDN; browsers with DevTools
        // open will attempt to fetch those .map files via connect-src.
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
            "img-src 'self' data: https:",
            "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "connect-src 'self' https://wttr.in https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "frame-src https://www.google.com",
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

        // Cache-Control: Prevent browser from caching authenticated pages (PDPA compliance).
        // Only applied when the user has an active session to avoid impacting public pages.
        if (session()->has('id')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
