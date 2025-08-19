<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateTrainees extends Command
{
    protected $signature = 'update:trainees';
    protected $description = 'Update trainee IDs and disability types according to welcome page';

    public function handle()
    {
        try {
            // Define disability type mappings based on welcome page services
            $disabilityMap = [
                'Autism Spectrum Disorder' => 'AUT',
                'Autism' => 'AUT',
                'ADHD' => 'LEA', // Learning support
                'Hearing Impairment' => 'HEA',
                'Visual Impairment' => 'VIS', 
                'Physical Disability' => 'PHY',
                'Physical Disabilities' => 'PHY',
                'Cerebral Palsy' => 'PHY',
                'Learning Disabilities' => 'LEA',
                'Learning Disability' => 'LEA',
                'Speech and Language Disorders' => 'SPE',
                'Speech and Language Disorder' => 'SPE',
                'Down Syndrome' => 'LEA', // Often involves learning support
                'Multiple Disabilities' => 'PHY', // Default to physical for multiple
                'Intellectual Disability' => 'LEA',
            ];

            // Update disability types to match welcome page services
            $serviceTypes = [
                'AUT' => 'Autism Spectrum Support',
                'HEA' => 'Hearing Impairment', 
                'VIS' => 'Visual Impairment',
                'PHY' => 'Physical Disabilities',
                'LEA' => 'Learning Support',
                'SPE' => 'Speech Therapy'
            ];

            $trainees = DB::table('trainees')->get();
            $counters = [];
            
            foreach ($trainees as $trainee) {
                // Map current condition to service type
                $currentCondition = $trainee->trainee_condition;
                $prefix = 'LEA'; // Default to learning support
                
                foreach ($disabilityMap as $oldCondition => $newPrefix) {
                    if (stripos($currentCondition, $oldCondition) !== false) {
                        $prefix = $newPrefix;
                        break;
                    }
                }
                
                // Generate new trainee ID
                if (!isset($counters[$prefix])) {
                    $counters[$prefix] = 1001; // Start from 1001
                } else {
                    $counters[$prefix]++;
                }
                
                $newTraineeId = $prefix . $counters[$prefix];
                $newCondition = $serviceTypes[$prefix];
                
                // Update trainee
                DB::table('trainees')
                    ->where('id', $trainee->id)
                    ->update([
                        'trainee_id' => $newTraineeId,
                        'trainee_condition' => $newCondition
                    ]);
                    
                $this->info("Updated {$trainee->trainee_first_name} {$trainee->trainee_last_name}: {$trainee->trainee_id} -> {$newTraineeId} | {$currentCondition} -> {$newCondition}");
            }
            
            $this->info("\n✅ All trainee IDs and conditions updated successfully!");
            
            // Show summary
            $this->info("\n📊 Summary by disability type:");
            foreach ($counters as $prefix => $count) {
                $actualCount = $count - 1000;
                $this->info("   {$prefix} ({$serviceTypes[$prefix]}): {$actualCount} trainees");
            }
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}