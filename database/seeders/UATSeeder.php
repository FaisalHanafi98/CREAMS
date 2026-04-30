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
        $this->command->info('  centres:  ' . Centre::where('centre_id', 'like', 'UA%')->count());
        $this->command->info('  staff:    ' . User::where('email', 'like', '%@' . self::UAT_EMAIL_DOMAIN)->count());
        $this->command->info('  trainees: ' . Trainee::where('trainee_id', 'like', 'UAT-%')->count());
        $this->command->info('  activities: ' . Activity::where('activity_name', 'like', 'UAT %')->count());
        $this->command->info('');
        $this->command->info('UAT login: any seeded staff email + password ' . self::UAT_PASS);
        $this->command->info('Sample logins:');
        $this->command->info('  admin:      super.admin@' . self::UAT_EMAIL_DOMAIN);
        $this->command->info('  supervisor: supervisor.a1@' . self::UAT_EMAIL_DOMAIN);
        $this->command->info('  teacher:    teacher.a1@' . self::UAT_EMAIL_DOMAIN);
        $this->command->info('  ajk:        ajk.a1@' . self::UAT_EMAIL_DOMAIN);
    }
}
