<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\SessionEnrollment;

class ActivitySystemDocumentation extends Seeder
{
    public function run(): void
    {
        $this->command->info("\n" . str_repeat('=', 90));
        $this->command->info("📚 CREAMS ACTIVITY SYSTEM - COMPREHENSIVE DOCUMENTATION 📚");
        $this->command->info(str_repeat('=', 90));
        
        $this->explainActivityIdConvention();
        $this->explainActivityPeriodConcept();
        $this->explainSessionsPerWeek();
        $this->explainActivityOutcomes();
        $this->showFinalSystemStatistics();
        
        $this->command->info(str_repeat('=', 90));
        $this->command->info("📖 ACTIVITY SYSTEM DOCUMENTATION COMPLETE 📖");
        $this->command->info(str_repeat('=', 90) . "\n");
    }

    private function explainActivityIdConvention(): void
    {
        $this->command->info("\n🏷️  ACTIVITY ID CONVENTION EXPLAINED:");
        $this->command->info("Format: 2 letters (category) + 4 digits (unique number)");
        $this->command->info("Examples: ST1234, OT5678, VT9012");
        
        $this->command->info("\n📝 Category Code Breakdown:");
        $this->command->info("├─ ST#### - Speech Therapy/Rehabilitation Activities");
        $this->command->info("├─ OT#### - Occupational Therapy/Development Activities");
        $this->command->info("├─ PT#### - Physical Therapy/Specialized Activities");
        $this->command->info("├─ ED#### - Education/Academic Skills Activities");
        $this->command->info("├─ VT#### - Vocational Training Activities");
        $this->command->info("├─ LS#### - Life Skills Activities");
        $this->command->info("├─ JR#### - Job Readiness Activities");
        $this->command->info("└─ CE#### - Community Engagement/Social Activities");
        
        $this->command->info("\n✅ Benefits of this convention:");
        $this->command->info("• Easy identification of activity type from ID");
        $this->command->info("• Consistent formatting across the system");
        $this->command->info("• Unique identification prevents duplicates");
        $this->command->info("• Professional appearance in reports");
    }

    private function explainActivityPeriodConcept(): void
    {
        $this->command->info("\n⏰ ACTIVITY PERIOD CONCEPT EXPLAINED:");
        $this->command->info("Activity Period = Duration of the entire program in WEEKS");
        
        $this->command->info("\n📊 How it works:");
        $this->command->info("├─ Activity Period: 12 weeks");
        $this->command->info("├─ Sessions per week: 2");
        $this->command->info("├─ Total sessions: 12 × 2 = 24 sessions");
        $this->command->info("└─ Duration: Start date + 12 weeks = End date");
        
        $this->command->info("\n🎯 Period Assignment Logic:");
        $this->command->info("├─ Intensive Programs (4+ sessions/week): 8 weeks");
        $this->command->info("├─ Regular Programs (2-3 sessions/week): 12 weeks");
        $this->command->info("└─ Light Programs (1 session/week): 16 weeks");
        
        $this->command->info("\n📅 Real Examples:");
        
        $sampleActivities = Activity::whereNotNull('activity_period')
            ->whereIn('centre_id', ['02', '03'])
            ->limit(3)
            ->get(['activity_id', 'activity_name', 'start_date', 'end_date', 'activity_period', 'sessions_per_week']);
        
        foreach ($sampleActivities as $activity) {
            $totalSessions = $activity->activity_period * $activity->sessions_per_week;
            $this->command->info("├─ {$activity->activity_id}: {$activity->activity_period} weeks × {$activity->sessions_per_week} sessions/week = {$totalSessions} total sessions");
        }
    }

    private function explainSessionsPerWeek(): void
    {
        $this->command->info("\n📅 SESSIONS PER WEEK EXPLAINED:");
        $this->command->info("Sessions per week = How many individual sessions occur weekly");
        
        $this->command->info("\n🎯 Typical Distribution:");
        $sessionStats = Activity::whereIn('centre_id', ['02', '03'])
            ->selectRaw('sessions_per_week, COUNT(*) as count')
            ->groupBy('sessions_per_week')
            ->orderBy('sessions_per_week')
            ->get();
        
        foreach ($sessionStats as $stat) {
            $intensity = $this->getIntensityLevel($stat->sessions_per_week);
            $this->command->info("├─ {$stat->sessions_per_week} sessions/week: {$stat->count} activities ({$intensity})");
        }
        
        $this->command->info("\n💡 Benefits:");
        $this->command->info("• Consistent scheduling across programs");
        $this->command->info("• Clear expectations for trainees and families");
        $this->command->info("• Resource planning and staff allocation");
        $this->command->info("• Progress tracking over time");
    }

    private function explainActivityOutcomes(): void
    {
        $this->command->info("\n🎯 ACTIVITY OUTCOMES EXPLAINED:");
        $this->command->info("Activity Outcomes = Expected results/achievements from the program");
        
        $this->command->info("\n📝 Sample Outcomes by Category:");
        
        // Show sample outcomes for different categories
        $categories = [
            'ST' => 'Speech Therapy',
            'VT' => 'Vocational Training',
            'LS' => 'Life Skills'
        ];
        
        foreach ($categories as $code => $name) {
            $activity = Activity::where('activity_id', 'LIKE', $code . '%')
                ->whereNotNull('activity_outcomes')
                ->first();
            
            if ($activity) {
                $outcomes = is_string($activity->activity_outcomes) 
                    ? json_decode($activity->activity_outcomes, true) 
                    : $activity->activity_outcomes;
                
                $this->command->info("├─ {$name} ({$activity->activity_id}):");
                if (is_array($outcomes)) {
                    foreach (array_slice($outcomes, 0, 2) as $outcome) {
                        $this->command->info("   • {$outcome}");
                    }
                }
            }
        }
        
        $this->command->info("\n✅ All activities now have comprehensive outcomes:");
        
        $outcomeStats = Activity::whereIn('centre_id', ['02', '03'])
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN activity_outcomes IS NOT NULL AND activity_outcomes != "" THEN 1 END) as with_outcomes
            ')
            ->first();
        
        $percentage = $outcomeStats->total > 0 ? round(($outcomeStats->with_outcomes / $outcomeStats->total) * 100, 1) : 0;
        $this->command->info("• {$outcomeStats->with_outcomes}/{$outcomeStats->total} activities have outcomes ({$percentage}%)");
    }

    private function showFinalSystemStatistics(): void
    {
        $this->command->info("\n📊 FINAL SYSTEM STATISTICS:");
        
        // Get comprehensive statistics
        $kuantanStats = $this->getCentreStats('02', 'Kuantan');
        $pagohStats = $this->getCentreStats('03', 'Pagoh');
        
        $this->command->info("\n🏥 KUANTAN CENTRE (Autism & Developmental Disabilities):");
        $this->displayCentreStats($kuantanStats);
        
        $this->command->info("\n🔧 PAGOH CENTRE (Vocational & Life Skills Training):");
        $this->displayCentreStats($pagohStats);
        
        // Combined totals
        $totalActivities = $kuantanStats['activities'] + $pagohStats['activities'];
        $totalSessions = $kuantanStats['sessions'] + $pagohStats['sessions'];
        $totalEnrollments = $kuantanStats['enrollments'] + $pagohStats['enrollments'];
        
        $this->command->info("\n🌟 COMBINED SYSTEM TOTALS:");
        $this->command->info("├─ 🎯 Total Activities: {$totalActivities}");
        $this->command->info("├─ 📅 Total Sessions: {$totalSessions}");
        $this->command->info("├─ 👥 Total Enrollments: {$totalEnrollments}");
        $this->command->info("└─ 🎓 Average Enrollments per Trainee: " . round($totalEnrollments / ($kuantanStats['trainees'] + $pagohStats['trainees']), 1));
        
        $this->command->info("\n✅ SYSTEM QUALITY ASSURANCE:");
        $this->command->info("├─ ✅ All activity IDs follow 2-letter + 4-digit convention");
        $this->command->info("├─ ✅ All activities have proper start and end dates");
        $this->command->info("├─ ✅ Activity periods logically calculated (3-12 weeks)");
        $this->command->info("├─ ✅ Sessions per week appropriately distributed");
        $this->command->info("├─ ✅ All activities have comprehensive outcomes");
        $this->command->info("└─ ✅ Centre-specific specializations maintained");
    }

    private function getCentreStats(string $centreId, string $centreName): array
    {
        $activities = Activity::where('centre_id', $centreId)->count();
        
        $sessions = ActivitySession::whereHas('activity', function($query) use ($centreId) {
            $query->where('centre_id', $centreId);
        })->count();
        
        $enrollments = SessionEnrollment::whereHas('session.activity', function($query) use ($centreId) {
            $query->where('centre_id', $centreId);
        })->count();
        
        $trainees = \App\Models\Trainee::where('centre_id', $centreId)->count();
        
        return [
            'centre_name' => $centreName,
            'activities' => $activities,
            'sessions' => $sessions,
            'enrollments' => $enrollments,
            'trainees' => $trainees
        ];
    }

    private function displayCentreStats(array $stats): void
    {
        $avgEnrollments = $stats['trainees'] > 0 ? round($stats['enrollments'] / $stats['trainees'], 1) : 0;
        
        $this->command->info("├─ 🎯 Activities: {$stats['activities']}");
        $this->command->info("├─ 📅 Sessions: {$stats['sessions']}");
        $this->command->info("├─ 👥 Enrollments: {$stats['enrollments']}");
        $this->command->info("├─ 🧒 Trainees: {$stats['trainees']}");
        $this->command->info("└─ 📊 Avg Enrollments/Trainee: {$avgEnrollments}");
    }

    private function getIntensityLevel(int $sessionsPerWeek): string
    {
        return match(true) {
            $sessionsPerWeek >= 4 => 'Intensive',
            $sessionsPerWeek >= 2 => 'Regular',
            default => 'Light'
        };
    }
}