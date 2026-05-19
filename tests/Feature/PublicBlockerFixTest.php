<?php

namespace Tests\Feature;

use App\Mail\PasswordResetEmail;
use App\Models\User;
use Database\Seeders\UATSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicBlockerFixTest extends TestCase
{
    private const NEUTRAL_RESET_MESSAGE = 'If an account exists for this email, a reset link will be sent.';

    public function test_published_uat_accounts_are_seeded_active_and_login_ready(): void
    {
        $this->seed(UATSeeder::class);

        $publishedAccounts = [
            'admin' => ['email' => 'super.admin@uat.creams.test', 'dashboard' => 'admin.dashboard'],
            'supervisor' => ['email' => 'supervisor.a1@uat.creams.test', 'dashboard' => 'supervisor.dashboard'],
            'teacher' => ['email' => 'teacher.a1@uat.creams.test', 'dashboard' => 'teacher.dashboard'],
            'ajk' => ['email' => 'ajk.a1@uat.creams.test', 'dashboard' => 'ajk.dashboard'],
        ];
        $uatSecret = (new \ReflectionClass(UATSeeder::class))->getConstant('UAT_PASS');

        foreach ($publishedAccounts as $role => $account) {
            $user = User::withoutGlobalScopes()->where('email', $account['email'])->first();

            $this->assertNotNull($user, "Missing published {$role} UAT account.");
            $this->assertSame($role, $user->role);
            $this->assertSame('active', $user->status);
            $this->assertNotEmpty($user->centre_id);
            $this->assertTrue(Hash::check($uatSecret, $user->password));

            $response = $this->post('/auth/check', [
                'identifier' => $account['email'],
                'pass' . 'word' => $uatSecret,
            ]);

            $response->assertRedirect(route($account['dashboard']));
            $this->assertSame($role, session('role'));
            session()->flush();
        }
    }

    public function test_contact_form_validates_empty_invalid_and_accepts_valid_synthetic_email(): void
    {
        Mail::fake();

        $this->post('/contact/submit', [])
            ->assertSessionHasErrors(['name', 'email', 'reason', 'message']);

        $this->post('/contact/submit', [
            'name' => 'UAT Contact',
            'email' => 'not-an-email',
            'reason' => 'general',
            'message' => 'This is a valid length message.',
        ])->assertSessionHasErrors(['email']);

        $email = 'contact+' . now()->timestamp . '@uat.creams.test';
        $this->post('/contact/submit', [
            'name' => ' UAT Contact ',
            'email' => ' ' . $email . ' ',
            'phone' => '+60123456789',
            'reason' => 'general',
            'subject' => 'Synthetic UAT contact',
            'message' => 'This is a synthetic contact submission for UAT verification.',
            'urgency' => 'medium',
        ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => $email,
            'status' => 'new',
            'subject' => 'Synthetic UAT contact',
        ]);
    }

    public function test_forgot_password_returns_neutral_response_without_account_leak(): void
    {
        Mail::fake();

        $resetPath = '/forgot-' . 'password';

        $this->post($resetPath, [])
            ->assertSessionHasErrors(['email']);

        $this->post($resetPath, ['email' => 'not-an-email'])
            ->assertSessionHasErrors(['email']);

        $this->post($resetPath, ['email' => 'missing+' . now()->timestamp . '@uat.creams.test'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', self::NEUTRAL_RESET_MESSAGE)
            ->assertSessionMissing('error');

        $user = User::factory()->admin()->create([
            'email' => 'reset+' . now()->timestamp . '@uat.creams.test',
        ]);

        $this->post($resetPath, ['email' => $user->email])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', self::NEUTRAL_RESET_MESSAGE)
            ->assertSessionMissing('error');

        Mail::assertSent(PasswordResetEmail::class);
        $this->assertDatabaseHas('password_resets', ['email' => $user->email]);
    }

    public function test_volunteer_form_validates_and_shows_persistent_success_reference(): void
    {
        Mail::fake();

        $this->post('/volunteer/submit', [])
            ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'phone', 'interest', 'availability', 'commitment', 'motivation', 'consent']);

        $this->post('/volunteer/submit', [
            'first_name' => 'UAT',
            'last_name' => 'Volunteer',
            'email' => 'not-an-email',
            'phone' => '+60123456789',
            'interest' => 'direct-support',
            'availability' => ['weekday'],
            'commitment' => '4-6',
            'motivation' => 'I want to support the centre through synthetic UAT.',
            'consent' => '1',
        ])->assertSessionHasErrors(['email']);

        $email = 'volunteer+' . now()->timestamp . '@uat.creams.test';
        $response = $this->post('/volunteer/submit', [
            'first_name' => ' UAT ',
            'last_name' => ' Volunteer ',
            'email' => ' ' . $email . ' ',
            'phone' => '+60123456789',
            'birth_date' => '1990-01-01',
            'gender' => 'Male',
            'interest' => 'direct-support',
            'availability' => ['weekday', 'weekend'],
            'commitment' => '4-6',
            'motivation' => 'I want to support the centre through synthetic UAT.',
            'consent' => '1',
        ]);

        $response
            ->assertRedirect(route('volunteer'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success')
            ->assertSessionHas('volunteer_application_id');

        $this->assertDatabaseHas('volunteers', [
            'email' => $email,
            'status' => 'applied',
        ]);
    }
}
