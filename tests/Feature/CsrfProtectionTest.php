<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * CSRF Protection Test Suite
 *
 * Verifies that CSRF protection is correctly implemented and prevents
 * unauthorized POST requests without valid CSRF tokens.
 *
 * Security Test Coverage:
 * - POST requests without CSRF token are rejected (419 status)
 * - GET requests do not require CSRF token
 * - CSRF token meta tag is present in HTML
 */
class CsrfProtectionTest extends TestCase
{
    /**
     * Test that POST requests without CSRF token are rejected
     *
     * @return void
     */
    public function test_post_request_without_csrf_token_is_rejected(): void
    {
        // Disable CSRF middleware for session creation (for test user login)
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Create a test user
        $user = User::factory()->create([
            'role' => 'admin',
            'centre_id' => 1
        ]);

        // Log in the user
        $this->actingAs($user);

        // Re-enable CSRF middleware for the actual test
        $this->app['router']->aliasMiddleware('web', \App\Http\Middleware\VerifyCsrfToken::class);

        // Attempt POST request without CSRF token (should be rejected)
        $response = $this->post('/profile/change-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        // Assert that request was rejected with 419 (CSRF token mismatch)
        $response->assertStatus(419);
    }

    /**
     * Test that POST requests with CSRF token are accepted
     *
     * @return void
     */
    public function test_post_request_with_csrf_token_is_accepted(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'role' => 'admin',
            'centre_id' => 1
        ]);

        // Log in the user
        $this->actingAs($user);

        // Attempt POST request with CSRF token (using Laravel's testing helper)
        // Note: This will still fail because password is incorrect, but should NOT be 419
        $response = $this->post('/profile/change-password', [
            '_token' => csrf_token(),
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        // Assert that request was NOT rejected for CSRF (should get redirect or validation error, not 419)
        $this->assertNotEquals(419, $response->status(), 'Request should not be rejected for CSRF when token is provided');
    }

    /**
     * Test that GET requests do not require CSRF token
     *
     * @return void
     */
    public function test_get_request_does_not_require_csrf_token(): void
    {
        // GET request to public route should work without CSRF token
        $response = $this->get('/');

        // Assert successful response (not 419)
        $this->assertNotEquals(419, $response->status());
    }

    /**
     * Test that CSRF token meta tag is present in HTML
     *
     * @return void
     */
    public function test_csrf_meta_tag_is_present_in_html(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'role' => 'admin',
            'centre_id' => 1
        ]);

        // Log in and get dashboard
        $response = $this->actingAs($user)->get('/dashboard');

        // Assert that CSRF token meta tag is present
        $response->assertSee('<meta name="csrf-token"', false);
    }

    /**
     * Test that forms include CSRF token field
     *
     * @return void
     */
    public function test_forms_include_csrf_token_field(): void
    {
        // Get login page
        $response = $this->get('/auth/login');

        // Assert that form has CSRF token input
        $response->assertSee('_token', false);
        $response->assertSee('csrf-token', false);
    }
}
