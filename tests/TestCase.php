<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseTransactions;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = false;

    /**
     * Override actingAs to set session data for custom auth middleware
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string|null  $guard
     * @return $this
     */
    public function actingAs($user, $guard = null)
    {
        $sessionData = [
            'id' => $user->id,
            'role' => $user->role ?? 'teacher',
            'centre_id' => $user->centre_id ?? '01',
            'name' => $user->name ?? 'Test User',
            'email' => $user->email ?? 'test@example.com',
            'iium_id' => $user->iium_id ?? null,
            'logged_in' => true,
        ];

        // Set session directly AND via withSession for CREAMS custom auth
        session($sessionData);

        return $this->withSession($sessionData);
    }
}
