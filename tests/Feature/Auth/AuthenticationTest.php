<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    private const AUTH_CHECK = '/auth/check';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_login_page_renders(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_auth_login_page_renders(): void
    {
        $response = $this->get('/auth/login');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::factory()->admin()->create([
            'password' => bcrypt('password'),
        ]);
        $this->post(self::AUTH_CHECK, [
            'identifier' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->get('/login');
        $response->assertRedirect(route('dashboard'));
    }

    public function test_login_while_authenticated_switches_user(): void
    {
        $teacher = User::factory()->teacher()->create([
            'password' => bcrypt('password'),
        ]);
        $admin = User::factory()->admin()->create([
            'password' => bcrypt('password'),
        ]);

        $this->post(self::AUTH_CHECK, [
            'identifier' => $teacher->email,
            'password' => 'password',
        ]);
        $this->assertEquals($teacher->id, session('id'));

        $this->post(self::AUTH_CHECK, [
            'identifier' => $admin->email,
            'password' => 'password',
        ]);
        $this->assertEquals($admin->id, session('id'));
        $this->assertEquals('admin', session('role'));
    }

    public function test_admin_can_login_via_email(): void
    {
        $user = User::factory()->admin()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(self::AUTH_CHECK, [
            'identifier' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertEquals($user->id, session('id'));
        $this->assertEquals('admin', session('role'));
        $this->assertEquals($user->centre_id, session('centre_id'));
    }

    public function test_supervisor_can_login_via_email(): void
    {
        $user = User::factory()->supervisor()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(self::AUTH_CHECK, [
            'identifier' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('supervisor.dashboard'));
        $this->assertEquals('supervisor', session('role'));
    }

    public function test_teacher_can_login_via_email(): void
    {
        $user = User::factory()->teacher()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(self::AUTH_CHECK, [
            'identifier' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('teacher.dashboard'));
        $this->assertEquals('teacher', session('role'));
    }

    public function test_ajk_can_login_via_email(): void
    {
        $user = User::factory()->ajk()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(self::AUTH_CHECK, [
            'identifier' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('ajk.dashboard'));
        $this->assertEquals('ajk', session('role'));
    }

    public function test_can_login_via_iium_id(): void
    {
        $user = User::factory()->teacher()->create([
            'iium_id' => 'TEST9999',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(self::AUTH_CHECK, [
            'identifier' => 'TEST9999',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('teacher.dashboard'));
        $this->assertEquals($user->id, session('id'));
    }

    public function test_invalid_password_rejected(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(self::AUTH_CHECK, [
            'identifier' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('auth.loginpage'));
        $this->assertNull(session('id'));
    }

    public function test_nonexistent_user_rejected(): void
    {
        $response = $this->post(self::AUTH_CHECK, [
            'identifier' => 'nobody@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('auth.loginpage'));
        $this->assertNull(session('id'));
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'status' => 'inactive',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(self::AUTH_CHECK, [
            'identifier' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('auth.loginpage'));
        $this->assertNull(session('id'));
    }

    public function test_session_data_set_correctly_after_login(): void
    {
        $user = User::factory()->admin()->create([
            'password' => bcrypt('password'),
        ]);

        $this->post(self::AUTH_CHECK, [
            'identifier' => $user->email,
            'password' => 'password',
        ]);

        $this->assertEquals($user->id, session('id'));
        $this->assertEquals($user->iium_id, session('iium_id'));
        $this->assertEquals($user->name, session('name'));
        $this->assertEquals($user->role, session('role'));
        $this->assertEquals($user->email, session('email'));
        $this->assertEquals($user->centre_id, session('centre_id'));
        $this->assertTrue(session('logged_in'));
    }

    public function test_empty_identifier_rejected(): void
    {
        $response = $this->post(self::AUTH_CHECK, [
            'identifier' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('identifier');
    }

    public function test_empty_password_rejected(): void
    {
        $response = $this->post(self::AUTH_CHECK, [
            'identifier' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
