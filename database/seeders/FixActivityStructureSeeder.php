<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FixActivityStructureSeeder extends Seeder
{
    /**
     * Category code mapping for activity IDs
     */
    private array $categoryCodeMapping = [
        1 => 'ST', // Speech Therapy / Rehabilitation
        2 => 'OT', // Occupational Therapy / Development Support
        3 => 'PT', // Physical Therapy / Specialized Therapy
        4 => 'ED', // Education / Academic Life Skills
        5 => 'VT', // Vocational Training
        6 => 'LS', // Life Skills
        7 => 'JR', // Job Readiness
        8 => 'CE', // Community Engagement / Social
    ];

    /**
     * Activity outcomes templates by category
     */
    private array $outcomeTemplates = [
        1 => [ // Speech Therapy / Rehabilitation
            "Improved verbal communication abilities demonstrated",
            "Enhanced articulation and speech clarity achieved",
            "Increased vocabulary and language comprehension",
            "Better social communication skills developed",
            "Progress in following verbal instructions shown"
        ],
        2 => [ // Occupational Therapy / Development Support
            "Enhanced fine motor skills and hand coordination",
            "Improved daily living skills and independence",
            "Better sensory processing and integration",
            "Increased attention span and focus abilities",
            "Progress in adaptive behavior skills shown"
        ],
        3 => [ // Physical Therapy / Specialized Therapy
            "Improved gross motor skills and mobility",
            "Enhanced balance and coordination abilities",
            "Better strength and endurance demonstrated",
            "Increased range of motion achieved",
            "Progress in postural control and stability"
        ],
        4 => [ // Education / Academic Life Skills
            "Academic readiness skills successfully developed",
            "Enhanced literacy and numeracy abilities",
            "Improved cognitive and learning skills",
            "Better problem-solving capabilities demonstrated",
            "Progress in educational goal achievement"
        ],
        5 => [ // Vocational Training
            "Job-specific skills successfully acquired",
            "Enhanced workplace readiness and behavior",
            "Improved technical competencies demonstrated",
            "Better understanding of safety protocols",
            "Progress in professional skill development"
        ],
        6 => [ // Life Skills
            "Independent living skills successfully developed",
            "Enhanced self-care and daily routines",
            "Improved home management capabilities",
            "Better community navigation skills",
            "Progress in personal responsibility shown"
        ],
        7 => [ // Job Readiness
            "Employment readiness skills acquired",
            "Enhanced interview and application abilities",
            "Improved workplace communication skills",
            "Better understanding of workplace expectations",
            "Progress in professional development achieved"
        ],
        8 => [ // Community Engagement / Social
            "Enhanced social interaction and communication",
            "Improved community participation skills",
            "Better cultural awareness and appreciation",
            "Increased civic responsibility understanding",
            "Progress in community integration shown"
        ]
    ];

    public function run(): void
    {
        $this->command->info('🔧 Starting comprehensive activity structure fix...');
        
        // Get all activities that need fixing (Kuantan and Pagoh)
        $activities = Activity::whereIn('centre_id', ['02', '03'])->get();
        
        $this->command->info("📊 Found {$activities->count()} activities to fix");
        
        $fixedCount = 0;
        
        foreach ($activities as $activity) {
            try {
                // 1. Fix Activity ID format
                $newActivityId = $this->generateCorrectActivityId($activity->category_id);
                
                // 2. Calculate proper start and end dates
                $dates = $this->calculateActivityDates($activity->sessions_per_week, $activity->activity_period);
                
                // 3. Ensure activity outcomes exist
                $outcomes = $this->ensureActivityOutcomes($activity->category_id, $activity->activity_outcomes);
                
                // Update the activity
                $activity->update([
                    'activity_id' => $newActivityId,
                    'start_date' => $dates['start_date'],
                    'end_date' => $dates['end_date'],
                    'activity_period' => $dates['period_weeks'],
                    'activity_outcomes' => $outcomes
                ]);
                
                $fixedCount++;
                
                if ($fixedCount % 50 == 0) {
                    $this->command->info("✅ Fixed {$fixedCount} activities so far...");
                }
                
            } catch (\Exception $e) {
                $this->command->warn("❌ Error fixing activity {$activity->id}: " . $e->getMessage());
            }
        }
        
        $this->showFixResults($fixedCount);
    }

    /**
     * Generate correct activity ID following the convention: 2 letters + 4 digits
     */
    private function generateCorrectActivityId(int $categoryId): string
    {
        $categoryCode = $this->categoryCodeMapping[$categoryId] ?? 'GN'; // GN = General
        
        // Generate unique 4-digit number
        do {
            $fourDigits = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $activityId = $categoryCode . $fourDigits;
            
            // Check if this ID already exists
            $exists = Activity::where('activity_id', $activityId)->exists();
        } while ($exists);
        
        return $activityId;
    }

    /**
     * Calculate proper start and end dates based on sessions per week and period
     */
    private function calculateActivityDates(int $sessionsPerWeek, ?int $activityPeriod): array
    {
        // If activity_period is null or invalid, calculate based on typical program lengths
        if (!$activityPeriod || $activityPeriod <= 0) {
            // Determine period based on sessions per week
            if ($sessionsPerWeek >= 4) {
                $periodWeeks = 8; // Intensive programs: 8 weeks
            } elseif ($sessionsPerWeek >= 2) {
                $periodWeeks = 12; // Regular programs: 12 weeks
            } else {
                $periodWeeks = 16; // Light programs: 16 weeks
            }
        } else {
            $periodWeeks = $activityPeriod;
        }
        
        // Calculate start date (between 1-4 weeks ago to 2 weeks in future)
        $startDate = Carbon::now()->subWeeks(rand(1, 4))->addWeeks(rand(0, 6));
        
        // Calculate end date based on the period
        $endDate = $startDate->copy()->addWeeks($periodWeeks);
        
        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'period_weeks' => $periodWeeks
        ];
    }

    /**
     * Ensure activity has proper outcomes
     */
    private function ensureActivityOutcomes(int $categoryId, $currentOutcomes): array
    {
        // If outcomes already exist and are valid, keep them
        if (!empty($currentOutcomes)) {
            if (is_string($currentOutcomes)) {
                $decodedOutcomes = json_decode($currentOutcomes, true);
                if (is_array($decodedOutcomes) && !empty($decodedOutcomes)) {
                    return $decodedOutcomes;
                }
            } elseif (is_array($currentOutcomes) && !empty($currentOutcomes)) {
                return $currentOutcomes;
            }
        }
        
        // Generate appropriate outcomes based on category
        $templates = $this->outcomeTemplates[$categoryId] ?? $this->outcomeTemplates[1];
        
        // Return 3-4 relevant outcomes
        return array_slice($templates, 0, rand(3, 4));
    }

    /**
     * Show comprehensive fix results
     */
    private function showFixResults(int $fixedCount): void
    {
        $this->command->info("\n" . str_repeat('=', 80));
        $this->command->info("🎉 ACTIVITY STRUCTURE FIX COMPLETED! 🎉");
        $this->command->info(str_repeat('=', 80));
        
        $this->command->info("✅ Total activities fixed: {$fixedCount}");
        
        // Show sample of fixed activities
        $this->command->info("\n📋 VERIFICATION - Sample Fixed Activities:");
        
        $sampleActivities = Activity::whereIn('centre_id', ['02', '03'])
            ->limit(5)
            ->get(['activity_id', 'activity_name', 'start_date', 'end_date', 'activity_period', 'sessions_per_week']);
        
        foreach ($sampleActivities as $activity) {
            $this->command->info("├─ ID: {$activity->activity_id} | Period: {$activity->activity_period} weeks | Sessions/week: {$activity->sessions_per_week}");
            $this->command->info("   Dates: {$activity->start_date} → {$activity->end_date}");
        }
        
        // Show ID format breakdown
        $this->command->info("\n🏷️  ACTIVITY ID FORMAT BREAKDOWN:");
        foreach ($this->categoryCodeMapping as $categoryId => $code) {
            $count = Activity::where('activity_id', 'LIKE', $code . '%')->count();
            $categoryName = $this->getCategoryName($categoryId);
            $this->command->info("├─ {$code}#### - {$categoryName}: {$count} activities");
        }
        
        // Show date distribution
        $this->command->info("\n📅 DATE DISTRIBUTION ANALYSIS:");
        
        $ongoing = Activity::whereIn('centre_id', ['02', '03'])
            ->whereDate('start_date', '<=', Carbon::now())
            ->whereDate('end_date', '>=', Carbon::now())
            ->count();
        
        $upcoming = Activity::whereIn('centre_id', ['02', '03'])
            ->whereDate('start_date', '>', Carbon::now())
            ->count();
        
        $completed = Activity::whereIn('centre_id', ['02', '03'])
            ->whereDate('end_date', '<', Carbon::now())
            ->count();
        
        $this->command->info("├─ 🟢 Ongoing Activities: {$ongoing}");
        $this->command->info("├─ 🔵 Upcoming Activities: {$upcoming}");
        $this->command->info("└─ ✅ Completed Activities: {$completed}");
        
        // Show activity period distribution
        $this->command->info("\n⏰ ACTIVITY PERIOD DISTRIBUTION:");
        $periodStats = Activity::whereIn('centre_id', ['02', '03'])
            ->selectRaw('activity_period, COUNT(*) as count')
            ->groupBy('activity_period')
            ->orderBy('activity_period')
            ->get();
        
        foreach ($periodStats as $stat) {
            $this->command->info("├─ {$stat->activity_period} weeks: {$stat->count} activities");
        }
        
        $this->command->info("\n💯 FIX SUMMARY:");
        $this->command->info("├─ ✅ Activity IDs: 2-letter + 4-digit format applied");
        $this->command->info("├─ ✅ Start/End Dates: Calculated based on sessions per week");
        $this->command->info("├─ ✅ Activity Period: Logical periods assigned (8-16 weeks)");
        $this->command->info("├─ ✅ Activity Outcomes: Comprehensive outcomes for all activities");
        $this->command->info("└─ ✅ Data Consistency: All activities now follow proper conventions");
        
        $this->command->info(str_repeat('=', 80) . "\n");
    }

    /**
     * Get category name by ID
     */
    private function getCategoryName(int $categoryId): string
    {
        $categoryNames = [
            1 => 'Speech Therapy/Rehabilitation',
            2 => 'Occupational Therapy/Development',
            3 => 'Physical Therapy/Specialized',
            4 => 'Education/Academic Skills',
            5 => 'Vocational Training',
            6 => 'Life Skills',
            7 => 'Job Readiness',
            8 => 'Community Engagement/Social'
        ];
        
        return $categoryNames[$categoryId] ?? 'General';
    }
}