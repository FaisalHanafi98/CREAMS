<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Users;
use App\Models\Centres;

class DiversifyCentres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'centres:diversify {--dry-run : Show what would be changed without updating the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Distribute users across different centres for better data diversity';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting centre diversification...');
        Log::info('Centre diversification command started');
        
        try {
            // Get all active centres
            $centres = Centres::where('centre_status', 'active')->get(['centre_id', 'centre_name']);
            
            if ($centres->isEmpty()) {
                $this->error('No active centres found in the database.');
                return 1;
            }
            
            $centreIds = $centres->pluck('centre_id')->toArray();
            $this->info('Found ' . count($centreIds) . ' active centres: ' . implode(', ', $centreIds));
            
            // Create a mapping of centre IDs to names
            $centreMap = $centres->pluck('centre_name', 'centre_id')->toArray();
            
            // Get all users
            $users = Users::all();
            $totalUsers = $users->count();
            
            if ($totalUsers === 0) {
                $this->error('No users found in the database.');
                return 1;
            }
            
            $this->info("Processing {$totalUsers} users...");
            
            // Calculate how many users to assign to each centre
            // We'll distribute them evenly, with a slight preference for main campuses
            $distribution = $this->calculateDistribution($totalUsers, $centreIds);
            
            $this->info('Planned distribution:');
            foreach ($distribution as $centreId => $count) {
                $this->info("- {$centreMap[$centreId]}: {$count} users");
            }
            
            // Check if this is a dry run
            $isDryRun = $this->option('dry-run');
            if ($isDryRun) {
                $this->warn('DRY RUN MODE: No changes will be made to the database.');
            }
            
            $progressBar = $this->output->createProgressBar($totalUsers);
            $progressBar->start();
            
            $updated = 0;
            $userIndex = 0;
            
            // Distribute users across centres according to our distribution plan
            foreach ($distribution as $centreId => $count) {
                for ($i = 0; $i < $count && $userIndex < $totalUsers; $i++) {
                    $user = $users[$userIndex];
                    $userIndex++;
                    
                    // Skip update if the user already has this centre
                    if ($user->centre_id === $centreId) {
                        $progressBar->advance();
                        continue;
                    }
                    
                    if (!$isDryRun) {
                        // Update the user's centre_id
                        $user->centre_id = $centreId;
                        // The setCentreIdAttribute mutator should handle updating centre_location
                        $user->save();
                    }
                    
                    $updated++;
                    $progressBar->advance();
                }
            }
            
            $progressBar->finish();
            $this->newLine();
            
            if ($isDryRun) {
                $this->info("Dry run completed: {$updated} users would be updated.");
            } else {
                $this->info("Centre diversification completed: {$updated} users were updated.");
                
                // Run the centres:sync command to ensure consistency
                $this->call('centres:sync');
            }
            
            Log::info('Centre diversification command completed', [
                'total_users' => $totalUsers,
                'updated_users' => $updated,
                'dry_run' => $isDryRun
            ]);
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error during diversification: ' . $e->getMessage());
            
            Log::error('Error during centre diversification command', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
    
    /**
     * Calculate the distribution of users across centres
     *
     * @param int $totalUsers
     * @param array $centreIds
     * @return array
     */
    private function calculateDistribution($totalUsers, $centreIds)
    {
        $distribution = [];
        $numCentres = count($centreIds);
        
        // Filter to only include main campuses
        $mainCentreIds = array_values(array_filter($centreIds, function($id) {
            return in_array($id, ['01', '02', '03', '04']); // Only Gombak, Kuantan, Gambang, Pagoh
        }));
        
        if (empty($mainCentreIds)) {
            $mainCentreIds = $centreIds; // Use all centres if no main ones found
        }
        
        // Distribute more evenly across main centers
        $weights = [
            '01' => 28, // Gombak - 28%
            '02' => 26, // Kuantan - 26%
            '03' => 24, // Gambang - 24%
            '04' => 22, // Pagoh - 22%
        ];
        
        // Adjust for missing centres
        $totalWeight = 0;
        foreach ($mainCentreIds as $centreId) {
            $totalWeight += $weights[$centreId] ?? 0;
        }
        
        // If no weights defined, distribute evenly
        if ($totalWeight === 0) {
            $baseCount = floor($totalUsers / count($mainCentreIds));
            $remainder = $totalUsers % count($mainCentreIds);
            
            foreach ($mainCentreIds as $index => $centreId) {
                $distribution[$centreId] = $baseCount + ($index < $remainder ? 1 : 0);
            }
            
            // Set others to 0
            foreach ($centreIds as $centreId) {
                if (!isset($distribution[$centreId])) {
                    $distribution[$centreId] = 0;
                }
            }
        } else {
            // Initialize all to 0
            foreach ($centreIds as $centreId) {
                $distribution[$centreId] = 0;
            }
            
            // Distribute according to weights for main centers
            $remainingUsers = $totalUsers;
            
            foreach ($mainCentreIds as $index => $centreId) {
                if ($index === count($mainCentreIds) - 1) {
                    // Last centre gets all remaining users
                    $distribution[$centreId] = $remainingUsers;
                } else {
                    $weight = $weights[$centreId] ?? 0;
                    $count = ($weight > 0) ? round(($weight / $totalWeight) * $totalUsers) : 0;
                    $distribution[$centreId] = $count;
                    $remainingUsers -= $count;
                }
            }
        }
        
        return $distribution;
    }
}