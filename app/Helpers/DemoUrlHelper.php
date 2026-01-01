<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;

/**
 * Helper class for generating demo instance aware URLs
 */
class DemoUrlHelper
{
    /**
     * Generate a URL for the current demo instance
     *
     * @param string $path The path (without /creams/{demo_id} prefix)
     * @param array $parameters Additional query parameters
     * @return string
     */
    public static function url(string $path = '', array $parameters = []): string
    {
        $demoId = config('app.demo_id', 'demo');
        $baseUrl = '/creams/' . $demoId;

        // Ensure path starts with /
        if ($path && $path[0] !== '/') {
            $path = '/' . $path;
        }

        $url = $baseUrl . $path;

        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        return $url;
    }

    /**
     * Generate a named route URL for the current demo instance
     *
     * @param string $name Route name
     * @param array $parameters Route parameters
     * @return string
     */
    public static function route(string $name, array $parameters = []): string
    {
        $demoId = config('app.demo_id', 'demo');
        $parameters['demo_id'] = $demoId;

        return route($name, $parameters);
    }

    /**
     * Get the current demo ID
     *
     * @return string
     */
    public static function getDemoId(): string
    {
        return config('app.demo_id', 'demo');
    }

    /**
     * Get the base URL for the current demo instance
     *
     * @return string
     */
    public static function baseUrl(): string
    {
        return '/creams/' . self::getDemoId();
    }
}
