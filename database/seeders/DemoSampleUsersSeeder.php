<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSampleUsersSeeder extends Seeder
{
    private const DEMO_PASS = 'DemoMode2026!';

    public function run(): void
    {
        // Resolve centre IDs dynamically so this seeder works in both the
        // local dev DB (UA1/UA2) and the production/test DB (01/02/03).
        $centreIds = DB::table('centres')->orderBy('centre_id')->pluck('centre_id');
        $c1 = $centreIds->get(0);
        $c2 = $centreIds->get(1) ?? $c1;

        if (!$c1) {
            // No centres exist yet — skip rather than violate FK constraint
            return;
        }

        $users = [
            [
                'name' => 'Mohd Izwan bin Mahmud',
                'email' => 'mohd.izwan.mahmud.gombak@iium.edu.my',
                'role' => 'admin',
                'centre_id' => $c1,
                'phone' => '+60-11-1000-0001',
            ],
            [
                'name' => 'Nur Azlina binti Razak',
                'email' => 'nur.azlina.razak.gombak@iium.edu.my',
                'role' => 'supervisor',
                'centre_id' => $c1,
                'phone' => '+60-11-1000-0002',
            ],
            [
                'name' => 'Nurul Iman binti Ali',
                'email' => 'nurul.iman.ali.gombak@iium.edu.my',
                'role' => 'supervisor',
                'centre_id' => $c1,
                'phone' => '+60-11-1000-0003',
            ],
            [
                'name' => 'Syed Ahmad bin Ibrahim',
                'email' => 'syed.ahmad.ibrahim.gombak@iium.edu.my',
                'role' => 'supervisor',
                'centre_id' => $c1,
                'phone' => '+60-11-1000-0004',
            ],
            [
                'name' => 'Fauziah Rahman binti Abdullah',
                'email' => 'fauziah.rahman.abdullah.kuantan@iium.edu.my',
                'role' => 'supervisor',
                'centre_id' => $c2,
                'phone' => '+60-11-1000-0005',
            ],
            [
                'name' => 'Muhammad Aidil bin Ismail',
                'email' => 'muhammad.aidil.ismail.kuantan@iium.edu.my',
                'role' => 'supervisor',
                'centre_id' => $c2,
                'phone' => '+60-11-1000-0006',
            ],
        ];

        foreach ($users as $user) {
            DB::table('staffs')->updateOrInsert(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'centre_id' => $user['centre_id'],
                    'phone' => $user['phone'],
                    'status' => 'active',
                    'password' => Hash::make(self::DEMO_PASS),
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
