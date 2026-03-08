<?php

namespace Database\Seeders\Test;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Centre;

class UserTestSeeder extends Seeder
{
    /**
     * Seed test users across different roles and centres.
     *
     * Creates:
     * - 5 admins (one per centre + one super admin)
     * - 4 supervisors (one per centre)
     * - 8 teachers (2 per centre)
     * - 3 AJKs (distributed across centres)
     *
     * Total: 20 users
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('Seeding test users...');

        $centres = Centre::all()->pluck('centre_id')->toArray();

        // 5 Admins - Full system access
        $this->command->info('Creating admins...');
        User::firstOrCreate(
            ['email' => 'admin.gombak@test.com'],
            [
                'name' => 'Admin Gombak',
                'role' => 'admin',
                'centre_id' => '01',
                'password' => 'password',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        User::firstOrCreate(
            ['email' => 'admin.kuantan@test.com'],
            [
                'name' => 'Admin Kuantan',
                'role' => 'admin',
                'centre_id' => '02',
                'password' => 'password',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        User::firstOrCreate(
            ['email' => 'admin.kl@test.com'],
            [
                'name' => 'Admin KL',
                'role' => 'admin',
                'centre_id' => '03',
                'password' => 'password',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        User::firstOrCreate(
            ['email' => 'admin.pagoh@test.com'],
            [
                'name' => 'Admin Pagoh',
                'role' => 'admin',
                'centre_id' => '04',
                'password' => 'password',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        User::firstOrCreate(
            ['email' => 'superadmin@test.com'],
            [
                'name' => 'Super Admin',
                'role' => 'admin',
                'centre_id' => '01',
                'password' => 'password',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 4 Supervisors - Centre-level management
        $this->command->info('Creating supervisors...');
        User::firstOrCreate(
            ['email' => 'supervisor.gombak@test.com'],
            ['name' => 'Supervisor Gombak', 'role' => 'supervisor', 'centre_id' => '01', 'password' => 'password', 'status' => 'active', 'email_verified_at' => now()]
        );
        User::firstOrCreate(
            ['email' => 'supervisor.kuantan@test.com'],
            ['name' => 'Supervisor Kuantan', 'role' => 'supervisor', 'centre_id' => '02', 'password' => 'password', 'status' => 'active', 'email_verified_at' => now()]
        );
        User::firstOrCreate(
            ['email' => 'supervisor.kl@test.com'],
            ['name' => 'Supervisor KL', 'role' => 'supervisor', 'centre_id' => '03', 'password' => 'password', 'status' => 'active', 'email_verified_at' => now()]
        );
        User::firstOrCreate(
            ['email' => 'supervisor.pagoh@test.com'],
            ['name' => 'Supervisor Pagoh', 'role' => 'supervisor', 'centre_id' => '04', 'password' => 'password', 'status' => 'active', 'email_verified_at' => now()]
        );

        // 8 Teachers - 2 per centre
        $this->command->info('Creating teachers...');
        $teachers = [
            ['Teacher Gombak A', 'teacher.gombak.a@test.com', '01'],
            ['Teacher Gombak B', 'teacher.gombak.b@test.com', '01'],
            ['Teacher Kuantan A', 'teacher.kuantan.a@test.com', '02'],
            ['Teacher Kuantan B', 'teacher.kuantan.b@test.com', '02'],
            ['Teacher KL A', 'teacher.kl.a@test.com', '03'],
            ['Teacher KL B', 'teacher.kl.b@test.com', '03'],
            ['Teacher Pagoh A', 'teacher.pagoh.a@test.com', '04'],
            ['Teacher Pagoh B', 'teacher.pagoh.b@test.com', '04'],
        ];
        foreach ($teachers as [$name, $email, $centreId]) {
            User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'role' => 'teacher', 'centre_id' => $centreId, 'password' => 'password', 'status' => 'active', 'email_verified_at' => now()]
            );
        }

        // 3 AJKs - Administrative support
        $this->command->info('Creating AJKs...');
        $ajks = [
            ['AJK Gombak', 'ajk.gombak@test.com', '01'],
            ['AJK Kuantan', 'ajk.kuantan@test.com', '02'],
            ['AJK KL', 'ajk.kl@test.com', '03'],
        ];
        foreach ($ajks as [$name, $email, $centreId]) {
            User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'role' => 'ajk', 'centre_id' => $centreId, 'password' => 'password', 'status' => 'active', 'email_verified_at' => now()]
            );
        }

        $this->command->info('✓ Created 20 test users (5 admins, 4 supervisors, 8 teachers, 3 AJKs)');
    }
}
