<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trainee;
use App\Models\Courses;

class BasicTraineesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧒 Creating basic trainees for all centres...');

        // Create 50 trainees for Gombak
        $this->command->info('Creating 50 trainees for Gombak...');
        for ($i = 1; $i <= 50; $i++) {
            $trainee = Trainee::create([
                'trainee_id' => 'GOM-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'trainee_first_name' => 'Ahmad' . $i,
                'trainee_last_name' => 'Rahman',
                'trainee_email' => 'trainee' . $i . '@gombak.creams.edu.my',
                'ic_number' => '0' . str_pad($i, 5, '0', STR_PAD_LEFT) . '-14-1234',
                'trainee_date_of_birth' => '2015-01-01',
                'gender' => 'Male',
                'trainee_phone_number' => '019-' . rand(1000000, 9999999),
                'trainee_address' => 'Gombak, Selangor',
                'trainee_condition' => 'Autism Spectrum Disorder',
                'centre_name' => 'Gombak',
                'centre_id' => '01',
                'guardian_name' => 'Parent ' . $i,
                'guardian_phone' => '019-' . rand(1000000, 9999999),
                'guardian_email' => 'parent' . $i . '@gmail.com',
                'guardian_relationship' => 'Father',
                'course_id' => 1
            ]);
            
            if ($i % 10 == 0) {
                $this->command->line("   Progress: $i/50");
            }
        }

        // Create 25 trainees for Kuantan
        $this->command->info('Creating 25 trainees for Kuantan...');
        for ($i = 1; $i <= 25; $i++) {
            $trainee = Trainee::create([
                'trainee_id' => 'KUA-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'trainee_first_name' => 'Siti' . $i,
                'trainee_last_name' => 'Ahmad',
                'trainee_email' => 'trainee' . $i . '@kuantan.creams.edu.my',
                'ic_number' => '1' . str_pad($i, 5, '0', STR_PAD_LEFT) . '-14-1234',
                'trainee_date_of_birth' => '2016-01-01',
                'gender' => 'Female',
                'trainee_phone_number' => '019-' . rand(1000000, 9999999),
                'trainee_address' => 'Kuantan, Pahang',
                'trainee_condition' => 'Down Syndrome',
                'centre_name' => 'Kuantan',
                'centre_id' => '02',
                'guardian_name' => 'Parent ' . $i,
                'guardian_phone' => '019-' . rand(1000000, 9999999),
                'guardian_email' => 'parent' . $i . '@gmail.com',
                'guardian_relationship' => 'Mother',
                'course_id' => 2
            ]);
            
            if ($i % 10 == 0) {
                $this->command->line("   Progress: $i/25");
            }
        }

        // Create 15 trainees for Pagoh
        $this->command->info('Creating 15 trainees for Pagoh...');
        for ($i = 1; $i <= 15; $i++) {
            $trainee = Trainee::create([
                'trainee_id' => 'PAG-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'trainee_first_name' => 'Lim' . $i,
                'trainee_last_name' => 'Wei',
                'trainee_email' => 'trainee' . $i . '@pagoh.creams.edu.my',
                'ic_number' => '2' . str_pad($i, 5, '0', STR_PAD_LEFT) . '-14-1234',
                'trainee_date_of_birth' => '2014-01-01',
                'gender' => 'Male',
                'trainee_phone_number' => '019-' . rand(1000000, 9999999),
                'trainee_address' => 'Pagoh, Johor',
                'trainee_condition' => 'Cerebral Palsy',
                'centre_name' => 'Pagoh',
                'centre_id' => '03',
                'guardian_name' => 'Parent ' . $i,
                'guardian_phone' => '019-' . rand(1000000, 9999999),
                'guardian_email' => 'parent' . $i . '@gmail.com',
                'guardian_relationship' => 'Father',
                'course_id' => 3
            ]);
            
            if ($i % 10 == 0) {
                $this->command->line("   Progress: $i/15");
            }
        }

        $totalTrainees = Trainee::count();
        $this->command->info("✅ Total trainees created: $totalTrainees");
        $this->command->info("   🏢 Gombak: 50 trainees");
        $this->command->info("   🏢 Kuantan: 25 trainees");
        $this->command->info("   🏢 Pagoh: 15 trainees");
    }
}