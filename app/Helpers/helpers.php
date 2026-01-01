<?php

use App\Helpers\DemoUrlHelper;

if (!function_exists('demo_url')) {
    /**
     * Generate a URL for the current demo instance
     *
     * @param string $path The path (without /creams/{demo_id} prefix)
     * @param array $parameters Additional query parameters
     * @return string
     */
    function demo_url(string $path = '', array $parameters = []): string
    {
        return DemoUrlHelper::url($path, $parameters);
    }
}

if (!function_exists('demo_route')) {
    /**
     * Generate a named route URL for the current demo instance
     *
     * @param string $name Route name
     * @param array $parameters Route parameters
     * @return string
     */
    function demo_route(string $name, array $parameters = []): string
    {
        return DemoUrlHelper::route($name, $parameters);
    }
}

if (!function_exists('demo_id')) {
    /**
     * Get the current demo instance ID
     *
     * @return string
     */
    function demo_id(): string
    {
        return DemoUrlHelper::getDemoId();
    }
}

if (!function_exists('demo_base')) {
    /**
     * Get the base URL for the current demo instance
     *
     * @return string
     */
    function demo_base(): string
    {
        return DemoUrlHelper::baseUrl();
    }
}
