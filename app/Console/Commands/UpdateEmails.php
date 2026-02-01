<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateEmails extends Command
{
    protected $signature = 'update:emails';
    protected $description = 'Update user emails to @iium.edu.my format';

    public function handle()
    {
        try {
            $users = DB::table('staffs')->get();
            $emailCounts = [];
            
            foreach ($users as $user) {
                // Generate base email
                $name = strtolower($user->name);
                $name = str_replace([' bin ', ' binti '], '.', $name);
                $name = str_replace(' ', '.', $name);
                $name = str_replace("'", '', $name);
                
                $baseEmail = $name . '@iium.edu.my';
                
                // Handle duplicates
                if (isset($emailCounts[$baseEmail])) {
                    $emailCounts[$baseEmail]++;
                    $finalEmail = str_replace('@iium.edu.my', $emailCounts[$baseEmail] . '@iium.edu.my', $baseEmail);
                } else {
                    $emailCounts[$baseEmail] = 1;
                    $finalEmail = $baseEmail;
                }
                
                // Update user
                DB::table('staffs')
                    ->where('id', $user->id)
                    ->update(['email' => $finalEmail]);
                    
                $this->info("Updated {$user->name} -> {$finalEmail}");
            }
            
            $this->info("\nAll user emails updated successfully!");
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}