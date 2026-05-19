<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Centre;
use App\Models\Staff;
use App\Models\Trainee;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UATSeeder extends Seeder
{
    private const UAT_PASS = 'UatPass2026!';
    private const UAT_EMAIL_DOMAIN = 'uat.creams.test';
    private const TRAINEES_PER_CENTRE = 20;
    private const ACTIVITIES_PER_CENTRE = 6;

    private \Faker\Generator $faker;

    public function __construct()
    {
        $this->faker = FakerFactory::create('ms_MY');
        $this->faker->seed(20260507);
    }

    public function run(): void
    {
        $this->command->info('UATSeeder: seeding realistic synthetic data for demo');

        DB::transaction(function () {
            $centres = $this->seedCentres();
            $this->seedStaff($centres);
            $this->call(DemoSampleUsersSeeder::class);
            $this->seedTrainees($centres);
            $this->seedActivities($centres);
            $this->seedSessions($centres);
            $this->seedEnrollments($centres);
            $this->seedSessionAttendance();
            $this->seedTraineeAttendances();
            $this->seedStaffAttendance($centres);
            $this->seedVolunteerApplications($centres);
        });

        $this->printSummary();
    }

    private function seedCentres(): array
    {
        $defs = [
            ['centre_id' => '01', 'centre_name' => 'Gombak', 'state' => 'Selangor', 'centre_address' => 'Jalan Gombak, 53100 Gombak, Selangor', 'centre_email' => 'gombak@' . self::UAT_EMAIL_DOMAIN],
            ['centre_id' => '02', 'centre_name' => 'Kuantan', 'state' => 'Pahang', 'centre_address' => 'Jalan Teluk Sisek, 25050 Kuantan, Pahang', 'centre_email' => 'kuantan@' . self::UAT_EMAIL_DOMAIN],
            ['centre_id' => '03', 'centre_name' => 'Pagoh', 'state' => 'Johor', 'centre_address' => 'Jalan Panchor, 84600 Pagoh, Johor', 'centre_email' => 'pagoh@' . self::UAT_EMAIL_DOMAIN],
        ];

        $centres = [];
        foreach ($defs as $def) {
            $centres[$def['centre_id']] = Centre::updateOrCreate(
                ['centre_id' => $def['centre_id']],
                [
                    'centre_name' => $def['centre_name'],
                    'state' => $def['state'],
                    'centre_address' => $def['centre_address'],
                    'centre_phone' => '+60-3-' . $this->faker->numerify('#### ####'),
                    'centre_email' => $def['centre_email'],
                    'centre_capacity' => 75,
                    'centre_status' => 'active',
                    'centre_description' => 'Synthetic stakeholder demo centre for CREAMS UAT.',
                    'is_active' => true,
                ]
            );
        }

        return $centres;
    }

    private function seedStaff(array $centres): void
    {
        $this->seedPublishedUatAccount('admin', 'UATA0001', 'super.admin@' . self::UAT_EMAIL_DOMAIN, 'UAT Super Admin', '01');
        $this->seedPublishedUatAccount('supervisor', 'UATS0001', 'supervisor.a1@' . self::UAT_EMAIL_DOMAIN, 'UAT Centre A Supervisor', '01');
        $this->seedPublishedUatAccount('teacher', 'UATT0001', 'teacher.a1@' . self::UAT_EMAIL_DOMAIN, 'UAT Centre A Teacher', '01');
        $this->seedPublishedUatAccount('ajk', 'UATJ0001', 'ajk.a1@' . self::UAT_EMAIL_DOMAIN, 'UAT Centre A AJK', '01');

        $rolesPerCentre = ['admin' => 1, 'supervisor' => 2, 'teacher' => 4, 'ajk' => 2];
        foreach ($centres as $centreId => $centre) {
            foreach ($rolesPerCentre as $role => $count) {
                for ($i = 1; $i <= $count; $i++) {
                    $email = sprintf('%s.%s.%d@%s', $role, strtolower($centre->centre_name), $i, self::UAT_EMAIL_DOMAIN);
                    Staff::updateOrCreate(
                        ['email' => $email],
                        [
                            'name' => $this->faker->name(),
                            'role' => $role,
                            'centre_id' => $centreId,
                            'position' => ucfirst($role),
                            'password' => Hash::make(self::UAT_PASS),
                            'status' => 'active',
                            'email_verified_at' => now(),
                            'phone' => '+60-1' . $this->faker->numerify('#-#### ####'),
                            'address' => $centre->centre_name . ', ' . $centre->state,
                        ]
                    );
                }
            }
        }
    }

    private function seedPublishedUatAccount(string $role, string $iiumId, string $email, string $name, string $centreId): void
    {
        Staff::updateOrCreate(
            ['email' => $email],
            [
                'iium_id' => $iiumId,
                'name' => $name,
                'role' => $role,
                'centre_id' => $centreId,
                'position' => ucfirst($role),
                'password' => Hash::make(self::UAT_PASS),
                'status' => 'active',
                'phone' => '+60-12-' . $this->faker->unique()->numerify('#######'),
                'email_verified_at' => now(),
                'address' => 'Synthetic UAT account for stakeholder browser testing.',
            ]
        );
    }

    private function seedTrainees(array $centres): void
    {
        $conditions = [
            'Autism Spectrum Support',
            'Learning Support',
            'Speech Therapy',
            'Physical Disabilities',
            'Hearing Impairment',
            'Visual Impairment',
        ];
        $relationships = ['Mother', 'Father', 'Guardian', 'Older Sibling', 'Aunt', 'Uncle'];

        foreach ($centres as $centreId => $centre) {
            for ($i = 1; $i <= self::TRAINEES_PER_CENTRE; $i++) {
                $traineeId = sprintf('UAT-%s-%03d', $centreId, $i);
                Trainee::updateOrCreate(
                    ['trainee_id' => $traineeId],
                    [
                        'trainee_first_name' => $this->faker->firstName(),
                        'trainee_last_name' => $this->faker->lastName(),
                        'trainee_email' => sprintf('trainee.%s.%03d@%s', strtolower($centre->centre_name), $i, self::UAT_EMAIL_DOMAIN),
                        'ic_number' => $this->generateFakeIc(),
                        'trainee_date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-6 years')->format('Y-m-d'),
                        'gender' => $this->faker->randomElement(['Male', 'Female']),
                        'trainee_phone_number' => '+60-1' . $this->faker->numerify('#-#### ####'),
                        'trainee_address' => $centre->centre_name . ', ' . $centre->state,
                        'trainee_condition' => $conditions[array_rand($conditions)],
                        'centre_id' => $centreId,
                        'centre_name' => $centre->centre_name,
                        'status' => 'active',
                        'guardian_name' => $this->faker->name(),
                        'guardian_phone' => '+60-1' . $this->faker->numerify('#-#### ####'),
                        'guardian_email' => sprintf('guardian.%s.%03d@%s', strtolower($centre->centre_name), $i, self::UAT_EMAIL_DOMAIN),
                        'guardian_relationship' => $relationships[array_rand($relationships)],
                        'photo_consent' => true,
                        'services_consent' => true,
                        'data_consent' => true,
                        'registration_date' => now()->subDays($this->faker->numberBetween(10, 280)),
                    ]
                );
            }
        }
    }

    private function seedActivities(array $centres): void
    {
        $categories = [
            'Autism Spectrum Support',
            'Learning Support',
            'Speech Therapy',
            'Physical Disabilities',
            'Hearing Impairment',
            'Visual Impairment',
        ];

        foreach ($centres as $centreId => $centre) {
            $teacher = Staff::where('centre_id', $centreId)->where('role', 'teacher')->first();
            for ($i = 0; $i < self::ACTIVITIES_PER_CENTRE; $i++) {
                $category = $categories[$i % count($categories)];
                Activity::updateOrCreate(
                    ['centre_id' => $centreId, 'activity_name' => $category . ' Program - ' . $centre->centre_name],
                    [
                        'activity_description' => 'Structured synthetic UAT program for ' . $category . '.',
                        'category' => $category,
                        'duration_weeks' => 12,
                        'sessions_per_week' => 2,
                        'session_duration_minutes' => 60,
                        'max_participants' => 12,
                        'activity_location' => $centre->centre_name . ' Therapy Room ' . ($i + 1),
                        'instructor_id' => $teacher?->id,
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function seedSessions(array $centres): void
    {
        foreach ($centres as $centreId => $centre) {
            $activities = DB::table('activities')->where('centre_id', $centreId)->get();
            $teacher = DB::table('staffs')->where('centre_id', $centreId)->where('role', 'teacher')->first();
            foreach ($activities as $idx => $activity) {
                $start = ($idx % 2 === 0) ? '09:00:00' : '14:00:00';
                $end = ($idx % 2 === 0) ? '10:00:00' : '15:00:00';

                for ($week = 4; $week >= 1; $week--) {
                    $date = now()->subWeeks($week)->startOfWeek()->addDays(1)->format('Y-m-d');
                    DB::table('activity_occurrences')->updateOrInsert(
                        ['activity_id' => $activity->id, 'session_date' => $date, 'start_time' => $start],
                        [
                            'session_name' => $activity->activity_name . ' - Week ' . (5 - $week),
                            'session_description' => 'Completed UAT session',
                            'end_time' => $end,
                            'location' => $centre->centre_name . ' Hall',
                            'instructor_id' => $teacher?->id,
                            'session_status' => 'completed',
                            'max_participants' => 12,
                            'current_participants' => 10,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    private function seedEnrollments(array $centres): void
    {
        $admin = DB::table('staffs')->where('role', 'admin')->first();
        foreach ($centres as $centreId => $centre) {
            $activities = DB::table('activities')->where('centre_id', $centreId)->get();
            $trainees = DB::table('trainees')->where('centre_id', $centreId)->limit(12)->get();
            foreach ($activities as $activity) {
                foreach ($trainees as $trainee) {
                    DB::table('activity_enrollments')->updateOrInsert(
                        ['activity_id' => $activity->id, 'trainee_id' => $trainee->id],
                        [
                            'enrollment_date' => now()->subMonths(2)->format('Y-m-d'),
                            'enrollment_status' => 'enrolled',
                            'enrollment_notes' => 'UAT synthetic enrollment',
                            'progress_percentage' => $this->faker->numberBetween(25, 90),
                            'attendance_count' => $this->faker->numberBetween(4, 10),
                            'enrolled_by' => $admin?->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    private function seedSessionAttendance(): void
    {
        $statuses = array_merge(array_fill(0, 8, 'present'), array_fill(0, 1, 'late'), array_fill(0, 1, 'absent'));
        $admin = DB::table('staffs')->where('role', 'admin')->first();
        $completedSessions = DB::table('activity_occurrences')->where('session_status', 'completed')->get();
        foreach ($completedSessions as $session) {
            $enrolled = DB::table('activity_enrollments')->where('activity_id', $session->activity_id)->pluck('trainee_id');
            foreach ($enrolled as $traineeId) {
                $status = $statuses[array_rand($statuses)];
                DB::table('session_attendance')->updateOrInsert(
                    ['session_id' => $session->id, 'trainee_id' => $traineeId],
                    [
                        'attendance_status' => $status,
                        'check_in_time' => $status === 'absent' ? null : ($status === 'late' ? '09:15:00' : '09:00:00'),
                        'notes' => null,
                        'marked_by' => $admin?->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function seedTraineeAttendances(): void
    {
        $admin = DB::table('staffs')->where('role', 'admin')->first();
        $rows = DB::table('session_attendance')
            ->join('activity_occurrences', 'session_attendance.session_id', '=', 'activity_occurrences.id')
            ->select(
                'session_attendance.trainee_id',
                'activity_occurrences.activity_id',
                'session_attendance.session_id',
                'activity_occurrences.session_date',
                'session_attendance.attendance_status as status'
            )
            ->get();

        foreach ($rows as $row) {
            DB::table('trainee_attendances')->updateOrInsert(
                ['trainee_id' => $row->trainee_id, 'activity_id' => $row->activity_id, 'session_id' => $row->session_id],
                [
                    'attendance_date' => $row->session_date,
                    'status' => $row->status,
                    'notes' => null,
                    'marked_by_user_id' => $admin?->id,
                    'marked_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function seedStaffAttendance(array $centres): void
    {
        $statuses = array_merge(array_fill(0, 18, 'present'), array_fill(0, 1, 'late'), array_fill(0, 1, 'absent'));
        foreach ($centres as $centreId => $centre) {
            $staff = DB::table('staffs')->where('centre_id', $centreId)->get();
            for ($day = 10; $day >= 1; $day--) {
                $date = now()->subWeekdays($day)->format('Y-m-d');
                foreach ($staff as $member) {
                    $status = $statuses[array_rand($statuses)];
                    DB::table('staff_attendances')->updateOrInsert(
                        ['user_id' => $member->id, 'attendance_date' => $date],
                        [
                            'centre_id' => $centreId,
                            'check_in_time' => $status === 'absent' ? null : ($status === 'late' ? '09:20:00' : '08:55:00'),
                            'status' => $status,
                            'approved' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    private function seedVolunteerApplications(array $centres): void
    {
        $centreIds = array_keys($centres);
        $statuses = ['applied', 'applied', 'approved', 'approved', 'approved', 'rejected', 'active', 'active'];
        $admin = DB::table('staffs')->where('role', 'admin')->first();

        foreach ($statuses as $idx => $status) {
            $createdAt = now()->subDays(($idx + 1) * 4);
            $reviewedAt = in_array($status, ['approved', 'rejected', 'active'], true) ? $createdAt->copy()->addDays(2) : null;
            $volunteerId = 'UAT-VOL-' . str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT);
            DB::table('volunteers')->updateOrInsert(
                ['volunteer_id' => $volunteerId],
                [
                    'volunteer_id' => $volunteerId,
                    'name' => $this->faker->name(),
                    'email' => sprintf('volunteer.%02d@%s', $idx + 1, self::UAT_EMAIL_DOMAIN),
                    'phone' => '+60-1' . $this->faker->numerify('#-#### ####'),
                    'address' => 'Malaysia',
                    'date_of_birth' => $this->faker->dateTimeBetween('-40 years', '-20 years')->format('Y-m-d'),
                    'gender' => $this->faker->randomElement(['Male', 'Female']),
                    'skills' => $this->faker->randomElement([
                        'Special education support, communication, classroom assistance',
                        'Occupational therapy assistant, sensory activity facilitation',
                        'Sports and mobility coaching, teamwork facilitation',
                    ]),
                    'availability' => $this->faker->randomElement(['Weekdays 9am-5pm', 'Weekends only', 'Flexible 2 days per week']),
                    'motivation' => 'I want to contribute to inclusive learning and rehabilitation support.',
                    'centre_id' => in_array($status, ['approved', 'active'], true) ? $centreIds[$idx % count($centreIds)] : null,
                    'status' => $status,
                    'registration_date' => $createdAt->format('Y-m-d'),
                    'reviewed_by' => $reviewedAt ? $admin?->id : null,
                    'review_notes' => $reviewedAt ? 'Synthetic UAT review.' : null,
                    'reviewed_at' => $reviewedAt,
                    'created_at' => $createdAt,
                    'updated_at' => $reviewedAt ?? $createdAt,
                ]
            );
        }
    }

    private function generateFakeIc(): string
    {
        $yymmdd = $this->faker->dateTimeBetween('-18 years', '-6 years')->format('ymd');
        return sprintf('%s-99-%s', $yymmdd, $this->faker->numerify('####'));
    }

    private function printSummary(): void
    {
        $this->command->info('UATSeeder complete');
        $this->command->info('centres: ' . Centre::whereIn('centre_id', ['01', '02', '03'])->count());
        $this->command->info('staffs: ' . DB::table('staffs')->count());
        $this->command->info('trainees: ' . DB::table('trainees')->count());
        $this->command->info('activities: ' . DB::table('activities')->count());
        $this->command->info('sessions: ' . DB::table('activity_occurrences')->count());
        $this->command->info('enrollments: ' . DB::table('activity_enrollments')->count());
        $this->command->info('session attendance: ' . DB::table('session_attendance')->count());
        $this->command->info('trainee attendance: ' . DB::table('trainee_attendances')->count());
        $this->command->info('staff attendance: ' . DB::table('staff_attendances')->count());
        $this->command->info('volunteers: ' . DB::table('volunteers')->count());
        $this->command->info('password for UAT users: ' . self::UAT_PASS);
    }
}
