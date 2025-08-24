<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class LetterSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📄 Seeding letters...');

        $faker = Faker::create();
        $templates = DB::table('letter_templates')->get();
        $trainees = DB::table('trainees')->get();
        $users = DB::table('users')->get();
        
        for ($i = 1; $i <= 30; $i++) {
            $template = $templates->random();
            $adminUsers = $users->where('role', 'admin');
            
            if ($adminUsers->count() === 0) {
                continue; // Skip if no admin users available
            }
            
            $generator = $adminUsers->random();
            
            if ($template->template_type === 'trainee') {
                $trainee = $trainees->random();
                $recipient = $trainee;
                $recipientType = 'trainee';
            } else {
                $user = $users->random();
                $recipient = $user;
                $recipientType = 'staff';
            }
            
            DB::table('letters')->insert([
                'letter_title' => $template->template_name . ' - ' . $faker->date(),
                'template_id' => $template->id,
                'letter_type' => $template->template_type,
                'letter_content' => $this->processTemplate($template->template_content, $recipient, $faker),
                'recipient_trainee_id' => $recipientType === 'trainee' ? $recipient->id : null,
                'recipient_user_id' => $recipientType === 'staff' ? $recipient->id : null,
                'recipient_name' => $recipientType === 'trainee' ? $recipient->trainee_first_name . ' ' . $recipient->trainee_last_name : $recipient->name,
                'recipient_email' => $recipientType === 'trainee' ? $recipient->trainee_email : $recipient->email,
                'generated_by' => $generator->id,
                'centre_id' => $generator->centre_id,
                'status' => $faker->randomElement(['generated', 'sent']),
                'generated_at' => $faker->dateTimeBetween('-2 months', 'now'),
                'sent_at' => $faker->boolean(70) ? $faker->dateTimeBetween('-1 month', 'now') : null,
                'notes' => 'Automatically generated letter',
                'created_at' => $faker->dateTimeBetween('-2 months', 'now'),
                'updated_at' => now()
            ]);
        }

        $this->command->info('📄 Successfully seeded 30 letters');
    }
    
    private function processTemplate($content, $recipient, $faker)
    {
        $replacements = [
            '{{guardian_name}}' => isset($recipient->guardian_name) ? $recipient->guardian_name : $faker->name,
            '{{trainee_name}}' => isset($recipient->trainee_first_name) ? $recipient->trainee_first_name . ' ' . $recipient->trainee_last_name : $recipient->name,
            '{{staff_name}}' => $recipient->name ?? $faker->name,
            '{{centre_name}}' => isset($recipient->centre_name) ? $recipient->centre_name : 'CREAMS Centre',
            '{{program_name}}' => 'Rehabilitation Program',
            '{{position}}' => isset($recipient->position) ? $recipient->position : 'Staff Member',
            '{{start_date}}' => $faker->date(),
            '{{completion_date}}' => $faker->date(),
            '{{month_year}}' => $faker->monthName . ' ' . $faker->year
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}