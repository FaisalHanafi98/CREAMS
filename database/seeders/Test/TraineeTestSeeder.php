<?php

namespace Database\Seeders\Test;

use Illuminate\Database\Seeder;
use App\Models\Trainee;
use App\Models\Centre;

class TraineeTestSeeder extends Seeder
{
    /**
     * Seed test trainees distributed across centres and disability types.
     *
     * Creates 50 trainees with realistic Malaysian data:
     * - Distributed across 4 centres (12-13 per centre)
     * - Various disability types (Autism, Down Syndrome, Cerebral Palsy, etc.)
     * - Ages 6-17 years old
     * - Realistic guardian information
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('Seeding test trainees...');

        $centres = Centre::all();
        $traineesPerCentre = [
            '01' => 13, // Gombak
            '02' => 12, // Kuantan
            '03' => 13, // KL
            '04' => 12, // Pagoh
        ];

        foreach ($centres as $centre) {
            $count = $traineesPerCentre[$centre->centre_id];

            $this->command->info("Creating {$count} trainees for {$centre->centre_name}...");

            // Create a mix of disability types
            $disabilityDistribution = [
                'Autism Spectrum Disorder' => ceil($count * 0.4), // 40% Autism
                'Down Syndrome' => ceil($count * 0.2),            // 20% Down Syndrome
                'Cerebral Palsy' => ceil($count * 0.15),          // 15% Cerebral Palsy
                'Intellectual Disability' => ceil($count * 0.15), // 15% Intellectual
                'Physical Disability' => ceil($count * 0.05),     // 5% Physical
                'Learning Disability' => ceil($count * 0.05),     // 5% Learning
            ];

            $created = 0;
            foreach ($disabilityDistribution as $disabilityType => $disabilityCount) {
                for ($i = 0; $i < $disabilityCount && $created < $count; $i++) {
                    Trainee::factory()->create([
                        'centre_id' => $centre->centre_id,
                        'centre_name' => $centre->centre_name,
                        'trainee_condition' => $disabilityType,
                    ]);
                    $created++;
                }
            }

            $this->command->info("✓ Created {$created} trainees for {$centre->centre_name}");
        }

        $this->command->info('✓ Created 50 test trainees across 4 centres');
    }
}
