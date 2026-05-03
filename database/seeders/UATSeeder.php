<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Centre;
use App\Models\Trainee;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * UATSeeder
 *
 * Standalone, anonymised seed for the 5-day UAT staging sprint.
 *
 * Data discipline:
 *   - All names, emails, phones, addresses, ICs are Faker-generated.
 *   - No real PPDK centre names. Centres are labelled "UAT Centre A/B/C".
 *   - Centre IDs use the UA1/UA2/UA3 prefix to avoid colliding with the
 *     production-style 01/02/03 centre codes used by CREAMSSeederFoundationManagement.
 *   - All staff use the same UAT password to simplify the recorded walkthrough.
 *   - IC numbers are constructed programmatically so no literal IC pattern appears in source.
 *   - Email domain is @uat.creams.test so seeded accounts are obvious in logs.
 *
 * Idempotent: re-runs reuse existing rows by stable identifier.
 *
 * Usage: php artisan db:seed --class=UATSeeder
 *        php artisan migrate:fresh --seed --seeder=UATSeeder
 */
class UATSeeder extends Seeder
{
    private const UAT_PASS = 'UatPass2026!';
    private const UAT_EMAIL_DOMAIN = 'uat.creams.test';
    private const TRAINEES_PER_CENTRE = 7;
    private const ACTIVITIES_PER_CENTRE = 3;

    private \Faker\Generator $faker;

    public function __construct()
    {
        $this->faker = FakerFactory::create();
        $this->faker->seed(20260501);
    }

    public function run(): void
    {
        $this->command->info('UATSeeder: starting anonymised UAT seed');

        DB::transaction(function () {
            $centres = $this->seedCentres();
            $this->seedStaff($centres);
            $this->seedTrainees($centres);
            $this->seedActivities($centres);
            $this->seedSessions($centres);
            $this->seedEnrollments($centres);
            $this->seedStaffAttendance($centres);
            $this->seedSessionAttendance();
            $this->seedTraineeAttendances();
        });

        $this->printSummary();
    }

    private function seedCentres(): array
    {
        $this->command->info('  centres...');

        $defs = [
            ['centre_id' => 'UA1', 'label' => 'A'],
            ['centre_id' => 'UA2', 'label' => 'B'],
            ['centre_id' => 'UA3', 'label' => 'C'],
        ];

        $centres = [];
        foreach ($defs as $def) {
            $centres[$def['centre_id']] = Centre::firstOrCreate(
                ['centre_id' => $def['centre_id']],
                [
                    'centre_name' => 'UAT Centre ' . $def['label'],
                    'state' => $this->faker->randomElement(['Selangor', 'Pulau Pinang', 'Johor']),
                    'centre_address' => $this->faker->streetAddress() . ', UAT District',
                    'centre_phone' => '+60-3-' . $this->faker->numerify('####-####'),
                    'centre_email' => 'centre.' . strtolower($def['label']) . '@' . self::UAT_EMAIL_DOMAIN,
                    'centre_capacity' => 50,
                    'centre_status' => 'active',
                    'centre_description' => 'UAT-only centre, anonymised data, not a real PPDK.',
                    'is_active' => true,
                ]
            );
        }

        return $centres;
    }

    private function seedStaff(array $centres): void
    {
        $this->command->info('  staff (admins, supervisors, teachers, AJKs)...');

        $superAdminEmail = 'super.admin@' . self::UAT_EMAIL_DOMAIN;
        User::firstOrCreate(
            ['email' => $superAdminEmail],
            [
                'name' => 'UAT Super Admin',
                'role' => 'admin',
                'centre_id' => 'UA1',
                'password' => self::UAT_PASS,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $rolesPerCentre = [
            'admin' => 1,
            'supervisor' => 1,
            'teacher' => 2,
            'ajk' => 1,
        ];

        foreach ($centres as $centreId => $centre) {
            $centreLabel = strtolower(substr($centre->centre_name, -1));

            foreach ($rolesPerCentre as $role => $count) {
                for ($i = 1; $i <= $count; $i++) {
                    $email = sprintf(
                        '%s.%s%d@%s',
                        $role,
                        $centreLabel,
                        $i,
                        self::UAT_EMAIL_DOMAIN
                    );

                    User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name' => sprintf('UAT %s %s%d', ucfirst($role), strtoupper($centreLabel), $i),
                            'role' => $role,
                            'centre_id' => $centreId,
                            'password' => self::UAT_PASS,
                            'status' => 'active',
                            'email_verified_at' => now(),
                            'phone' => '+60-1' . $this->faker->numerify('#-####-####'),
                        ]
                    );
                }
            }
        }
    }

    private function seedTrainees(array $centres): void
    {
        $this->command->info('  trainees (' . self::TRAINEES_PER_CENTRE . ' per centre)...');

        foreach ($centres as $centreId => $centre) {
            for ($i = 1; $i <= self::TRAINEES_PER_CENTRE; $i++) {
                $traineeId = sprintf('UAT-%s-%03d', $centreId, $i);

                Trainee::firstOrCreate(
                    ['trainee_id' => $traineeId],
                    [
                        'trainee_first_name' => 'UAT' . $i,
                        'trainee_last_name' => $this->faker->lastName(),
                        'trainee_email' => sprintf('trainee.%s.%d@%s', strtolower($centreId), $i, self::UAT_EMAIL_DOMAIN),
                        'ic_number' => $this->generateFakeIc(),
                        'trainee_date_of_birth' => $this->faker->dateTimeBetween('-15 years', '-5 years')->format('Y-m-d'),
                        'gender' => $this->faker->randomElement(['Male', 'Female']),
                        'trainee_phone_number' => '+60-1' . $this->faker->numerify('#-####-####'),
                        'trainee_address' => $this->faker->streetAddress() . ', UAT District',
                        'trainee_condition' => $this->faker->randomElement([
                            'Autism Spectrum',
                            'Down Syndrome',
                            'Cerebral Palsy',
                            'Learning Disability',
                            'Speech Delay',
                        ]),
                        'centre_id' => $centreId,
                        'centre_name' => $centre->centre_name,
                        'status' => 'active',
                        'guardian_name' => 'UAT Guardian ' . $i,
                        'guardian_phone' => '+60-1' . $this->faker->numerify('#-####-####'),
                        'guardian_email' => sprintf('guardian.%s.%d@%s', strtolower($centreId), $i, self::UAT_EMAIL_DOMAIN),
                        'guardian_relationship' => $this->faker->randomElement(['Parent', 'Guardian']),
                        'photo_consent' => true,
                        'services_consent' => true,
                        'data_consent' => true,
                        'registration_date' => now()->subDays($this->faker->numberBetween(30, 365)),
                    ]
                );
            }
        }
    }

    private function seedActivities(array $centres): void
    {
        $this->command->info('  activities (' . self::ACTIVITIES_PER_CENTRE . ' per centre)...');

        $categories = [
            'Autism Spectrum Support',
            'Hearing Impairment',
            'Visual Impairment',
            'Physical Disabilities',
            'Learning Support',
            'Speech Therapy',
        ];

        $names = [
            'Sensory Play Workshop',
            'Speech Skills Group',
            'Motor Skills Circuit',
            'Communication Basics',
            'Music and Movement',
            'Art Therapy Session',
            'Social Skills Group',
            'Numeracy Foundation',
            'Reading Readiness',
        ];

        foreach ($centres as $centreId => $centre) {
            $teacher = User::where('centre_id', $centreId)->where('role', 'teacher')->first();
            $instructorId = $teacher ? $teacher->id : null;

            for ($i = 0; $i < self::ACTIVITIES_PER_CENTRE; $i++) {
                $name = $names[($i + array_search($centreId, array_keys($centres)) * 3) % count($names)];

                Activity::firstOrCreate(
                    [
                        'centre_id' => $centreId,
                        'activity_name' => 'UAT ' . $name . ' (' . $centreId . ')',
                    ],
                    [
                        'activity_description' => 'UAT-only activity for centre ' . $centre->centre_name . '. Anonymised seed data.',
                        'category' => $categories[$i % count($categories)],
                        'duration_weeks' => $this->faker->numberBetween(4, 12),
                        'sessions_per_week' => $this->faker->numberBetween(1, 3),
                        'session_duration_minutes' => 60,
                        'max_participants' => 10,
                        'activity_location' => 'UAT Centre ' . substr($centre->centre_name, -1) . ' Hall ' . ($i + 1),
                        'instructor_id' => $instructorId,
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function seedSessions(array $centres): void
    {
        $this->command->info('  activity sessions (3 past + 1 upcoming per activity)...');

        $sessionTimes = [['09:00:00', '10:00:00'], ['10:30:00', '11:30:00'], ['14:00:00', '15:00:00']];

        foreach ($centres as $centreId => $centre) {
            $activities = DB::table('activities')->where('centre_id', $centreId)->get();
            $teacher    = DB::table('staffs')->where('centre_id', $centreId)->where('role', 'teacher')->first();
            $instructorId = $teacher ? $teacher->id : null;

            foreach ($activities as $idx => $activity) {
                $times = $sessionTimes[$idx % count($sessionTimes)];

                // 3 completed past sessions (weekly, last 3 weeks)
                for ($week = 3; $week >= 1; $week--) {
                    $date = now()->subWeeks($week)->startOfWeek()->addDays(1)->format('Y-m-d');
                    DB::table('activity_occurrences')->updateOrInsert(
                        ['activity_id' => $activity->id, 'session_date' => $date, 'start_time' => $times[0]],
                        [
                            'session_name'        => $activity->activity_name . ' — Week ' . (4 - $week),
                            'session_description' => 'UAT session ' . (4 - $week) . ' of ' . $activity->activity_name,
                            'end_time'            => $times[1],
                            'location'            => 'UAT Centre ' . substr($centre->centre_name, -1) . ' Hall',
                            'instructor_id'       => $instructorId,
                            'session_status'      => 'completed',
                            'max_participants'    => 10,
                            'current_participants' => 5,
                            'created_at'          => now(),
                            'updated_at'          => now(),
                        ]
                    );
                }

                // 1 upcoming scheduled session (next week)
                $upcomingDate = now()->addWeek()->startOfWeek()->addDays(1)->format('Y-m-d');
                DB::table('activity_occurrences')->updateOrInsert(
                    ['activity_id' => $activity->id, 'session_date' => $upcomingDate, 'start_time' => $times[0]],
                    [
                        'session_name'        => $activity->activity_name . ' — Week 4',
                        'session_description' => 'UAT upcoming session for ' . $activity->activity_name,
                        'end_time'            => $times[1],
                        'location'            => 'UAT Centre ' . substr($centre->centre_name, -1) . ' Hall',
                        'instructor_id'       => $instructorId,
                        'session_status'      => 'scheduled',
                        'max_participants'    => 10,
                        'current_participants' => 0,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]
                );
            }
        }
    }

    private function seedEnrollments(array $centres): void
    {
        $this->command->info('  activity enrollments (5 trainees per activity)...');

        $admin = DB::table('staffs')->where('role', 'admin')->first();

        foreach ($centres as $centreId => $centre) {
            $activities = DB::table('activities')->where('centre_id', $centreId)->get();
            $trainees   = DB::table('trainees')->where('centre_id', $centreId)->limit(5)->get();

            foreach ($activities as $activity) {
                foreach ($trainees as $trainee) {
                    DB::table('activity_enrollments')->updateOrInsert(
                        ['activity_id' => $activity->id, 'trainee_id' => $trainee->id],
                        [
                            'enrollment_date'     => now()->subWeeks(4)->format('Y-m-d'),
                            'enrollment_status'   => 'enrolled',
                            'enrollment_notes'    => 'UAT enrollment — anonymised',
                            'progress_percentage' => $this->faker->numberBetween(30, 80),
                            'attendance_count'    => 3,
                            'enrolled_by'         => $admin ? $admin->id : null,
                            'created_at'          => now(),
                            'updated_at'          => now(),
                        ]
                    );
                }
            }
        }
    }

    private function seedStaffAttendance(array $centres): void
    {
        $this->command->info('  staff attendance (last 5 working days)...');

        $statusWeights = array_merge(array_fill(0, 18, 'present'), array_fill(0, 1, 'late'), array_fill(0, 1, 'absent'));

        foreach ($centres as $centreId => $centre) {
            $staff = DB::table('staffs')->where('centre_id', $centreId)->get();

            for ($day = 5; $day >= 1; $day--) {
                $date = now()->subWeekdays($day)->format('Y-m-d');

                foreach ($staff as $member) {
                    $status = $statusWeights[array_rand($statusWeights)];
                    DB::table('staff_attendances')->updateOrInsert(
                        ['user_id' => $member->id, 'attendance_date' => $date],
                        [
                            'centre_id'    => $centreId,
                            'check_in_time' => $status === 'absent' ? null : ($status === 'late' ? '09:20:00' : '08:55:00'),
                            'status'       => $status,
                            'approved'     => 1,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]
                    );
                }
            }
        }
    }

    private function seedTraineeAttendances(): void
    {
        $this->command->info('  trainee_attendances (mirror of session_attendance for Trainee model)...');

        $admin = DB::table('staffs')->where('role', 'admin')->first();

        $sessionAttendances = DB::table('session_attendance')
            ->join('activity_occurrences', 'session_attendance.session_id', '=', 'activity_occurrences.id')
            ->select(
                'session_attendance.trainee_id',
                'activity_occurrences.activity_id',
                'session_attendance.session_id',
                'activity_occurrences.session_date',
                'session_attendance.attendance_status as status'
            )
            ->get();

        foreach ($sessionAttendances as $record) {
            DB::table('trainee_attendances')->updateOrInsert(
                ['trainee_id' => $record->trainee_id, 'activity_id' => $record->activity_id, 'session_id' => $record->session_id],
                [
                    'attendance_date'  => $record->session_date,
                    'status'           => $record->status,
                    'notes'            => null,
                    'marked_by_user_id' => $admin ? $admin->id : null,
                    'marked_at'        => now(),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]
            );
        }
    }

    private function seedSessionAttendance(): void
    {
        $this->command->info('  session attendance (enrolled trainees in completed sessions)...');

        $statusWeights = array_merge(array_fill(0, 8, 'present'), array_fill(0, 1, 'absent'), array_fill(0, 1, 'late'));

        $completedSessions = DB::table('activity_occurrences')
            ->where('session_status', 'completed')
            ->get();

        $admin = DB::table('staffs')->where('role', 'admin')->first();

        foreach ($completedSessions as $session) {
            $enrolled = DB::table('activity_enrollments')
                ->where('activity_id', $session->activity_id)
                ->where('enrollment_status', 'enrolled')
                ->pluck('trainee_id');

            foreach ($enrolled as $traineeId) {
                $status = $statusWeights[array_rand($statusWeights)];
                DB::table('session_attendance')->updateOrInsert(
                    ['session_id' => $session->id, 'trainee_id' => $traineeId],
                    [
                        'attendance_status' => $status,
                        'check_in_time'     => $status === 'absent' ? null : ($status === 'late' ? '09:15:00' : '09:00:00'),
                        'notes'             => null,
                        'marked_by'         => $admin ? $admin->id : null,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]
                );
            }
        }
    }

    /**
     * Generate a fake IC number that does not collide with any real Malaysian IC.
     * State codes 70-99 are unused by the real IC scheme, so prefixing with 99 keeps
     * the format recognisable while making the value obviously synthetic.
     */
    private function generateFakeIc(): string
    {
        $yymmdd = $this->faker->dateTimeBetween('-15 years', '-5 years')->format('ymd');
        $stateCode = '99';
        $sequence = $this->faker->numerify('####');

        return sprintf('%s-%s-%s', $yymmdd, $stateCode, $sequence);
    }

    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('UATSeeder complete.');
        $this->command->info('  centres:     ' . Centre::where('centre_id', 'like', 'UA%')->count());
        $this->command->info('  staff:       ' . User::where('email', 'like', '%@' . self::UAT_EMAIL_DOMAIN)->count());
        $this->command->info('  trainees:    ' . Trainee::where('trainee_id', 'like', 'UAT-%')->count());
        $this->command->info('  activities:  ' . Activity::where('activity_name', 'like', 'UAT %')->count());
        $this->command->info('  sessions:    ' . DB::table('activity_occurrences')->count());
        $this->command->info('  enrollments: ' . DB::table('activity_enrollments')->count());
        $this->command->info('  staff_att:   ' . DB::table('staff_attendances')->count());
        $this->command->info('  sess_att:    ' . DB::table('session_attendance')->count());
        $this->command->info('  trainee_att: ' . DB::table('trainee_attendances')->count());
        $this->command->info('');
        $this->command->info('UAT login: any seeded staff email + password ' . self::UAT_PASS);
        $this->command->info('Sample logins:');
        $this->command->info('  admin:      super.admin@' . self::UAT_EMAIL_DOMAIN);
        $this->command->info('  supervisor: supervisor.a1@' . self::UAT_EMAIL_DOMAIN);
        $this->command->info('  teacher:    teacher.a1@' . self::UAT_EMAIL_DOMAIN);
        $this->command->info('  ajk:        ajk.a1@' . self::UAT_EMAIL_DOMAIN);
    }
}
