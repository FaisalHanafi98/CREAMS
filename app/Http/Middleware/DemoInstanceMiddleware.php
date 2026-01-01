<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Demo Instance Middleware
 *
 * Handles the demo_id parameter from URLs like:
 * /creams/{demo_id}/dashboard
 * /creams/{demo_id}/activities
 *
 * This allows multiple isolated demo instances to run simultaneously.
 */
class DemoInstanceMiddleware
{
    /**
     * Valid demo instance IDs
     * Add new demo instances here as needed
     */
    protected array $validDemoIds = [
        'demo',      // Default demo instance
        'demo1',     // Additional demo instances
        'demo2',
        'demo3',
        'staging',   // Staging environment
        'uat',       // UAT testing
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $demoId = $request->route('demo_id');

        // Validate demo ID
        if (!$this->isValidDemoId($demoId)) {
            abort(404, 'Invalid demo instance');
        }

        // Store demo_id in config for use throughout the application
        config(['app.demo_id' => $demoId]);

        // Set a unique session name per demo instance to isolate sessions
        config(['session.cookie' => 'creams_' . $demoId . '_session']);

        // Store in request for easy access
        $request->attributes->set('demo_id', $demoId);

        // Share with all views
        view()->share('demo_id', $demoId);
        view()->share('demo_base_url', '/creams/' . $demoId);

        return $next($request);
    }

    /**
     * Check if the demo ID is valid
     */
    protected function isValidDemoId(?string $demoId): bool
    {
        if (empty($demoId)) {
            return false;
        }

        // Allow any alphanumeric demo ID (for flexibility)
        // Or restrict to specific IDs by uncommenting the line below
        // return in_array($demoId, $this->validDemoIds);

        return preg_match('/^[a-zA-Z0-9_-]{1,32}$/', $demoId) === 1;
    }
}
