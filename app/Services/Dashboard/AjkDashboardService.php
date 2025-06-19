<?php

namespace App\Services\Dashboard;

use App\Models\Users;
use App\Models\Event;
use App\Models\Volunteers;
use App\Models\ContactMessages;
use App\Models\Asset;
use App\Models\Activity;
use App\Models\Centres;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class AjkDashboardService extends BaseDashboardService
{
    /**
     * Get dashboard data for AJK (Committee) users
     */
    public function getDashboardData(int $ajkId): array
    {
        return Cache::remember("dashboard_ajk_{$ajkId}", $this->cacheTimeout, function () use ($ajkId) {
            try {
                $ajk = Users::find($ajkId);
                if (!$ajk) {
                    throw new Exception("AJK user not found: {$ajkId}");
                }

                return [
                    'stats' => $this->getAjkStats($ajkId, $ajk),
                    'charts' => $this->getAjkCharts($ajkId),
                    'notifications' => $this->getNotifications($ajkId, 'ajk'),
                    'upcomingEvents' => $this->getUpcomingEvents(10),
                    'volunteerManagement' => $this->getVolunteerManagement($ajkId),
                    'communityEngagement' => $this->getCommunityEngagement($ajkId),
                    'assetOverview' => $this->getAssetOverview($ajkId),
                    'quickActions' => $this->getAjkQuickActions($ajkId),
                    'eventMetrics' => $this->getEventMetrics($ajkId),
                    'monthlyOverview' => $this->getMonthlyOverview($ajkId),
                    'centreCoordination' => $this->getCentreCoordination($ajkId),
                ];
            } catch (Exception $e) {
                Log::error('Error getting AJK dashboard data', [
                    'ajk_id' => $ajkId,
                    'error' => $e->getMessage()
                ]);
                
                return $this->getFallbackAjkData($ajkId);
            }
        });
    }

    /**
     * Get AJK-specific statistics
     */
    private function getAjkStats(int $ajkId, $ajk): array
    {
        try {
            return [
                'total_events' => Event::count(),
                'upcoming_events' => Event::where('event_date', '>', now())->count(),
                'active_volunteers' => Volunteers::where('status', 'approved')->count(),
                'pending_volunteers' => Volunteers::where('status', 'pending')->count(),
                'total_assets' => Asset::count(),
                'asset_value' => Asset::sum('value') ?? 0,
                'community_messages' => ContactMessages::count(),
                'unread_messages' => ContactMessages::where('status', 'unread')->count(),
                'events_this_month' => Event::whereMonth('event_date', Carbon::now()->month)->count(),
                'volunteer_participation' => $this->getVolunteerParticipationRate(),
                'centre_coordination_score' => $this->getCentreCoordinationScore(),
                'community_satisfaction' => $this->getCommunityFeedbackScore(),
            ];
        } catch (Exception $e) {
            Log::error('Error getting AJK stats', ['ajk_id' => $ajkId, 'error' => $e->getMessage()]);
            return [
                'total_events' => 0,
                'upcoming_events' => 0,
                'active_volunteers' => 0,
                'pending_volunteers' => 0,
                'total_assets' => 0,
                'asset_value' => 0,
                'community_messages' => 0,
                'unread_messages' => 0,
                'events_this_month' => 0,
                'volunteer_participation' => 0,
                'centre_coordination_score' => 0,
                'community_satisfaction' => 0,
            ];
        }
    }

    /**
     * Get AJK-specific charts
     */
    private function getAjkCharts(int $ajkId): array
    {
        try {
            return [
                'event_participation' => $this->getEventParticipationChart(),
                'volunteer_engagement' => $this->getVolunteerEngagementChart(),
                'community_feedback' => $this->getCommunityFeedbackChart(),
                'asset_utilization' => $this->getAssetUtilizationChart(),
                'centre_performance' => $this->getCentrePerformanceChart(),
            ];
        } catch (Exception $e) {
            Log::error('Error getting AJK charts', ['ajk_id' => $ajkId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get volunteer management data
     */
    private function getVolunteerManagement(int $ajkId): array
    {
        try {
            return [
                'active_volunteers' => Volunteers::where('status', 'approved')
                    ->get()
                    ->map(function ($volunteer) {
                        return [
                            'id' => $volunteer->id,
                            'name' => $volunteer->name,
                            'email' => $volunteer->email,
                            'skills' => $volunteer->skills ?? '',
                            'availability' => $volunteer->availability ?? '',
                            'join_date' => $volunteer->created_at,
                            'last_activity' => $this->getVolunteerLastActivity($volunteer->id),
                            'events_participated' => $this->getVolunteerEventCount($volunteer->id),
                        ];
                    })
                    ->toArray(),
                
                'pending_applications' => Volunteers::where('status', 'pending')
                    ->latest()
                    ->get()
                    ->map(function ($volunteer) {
                        return [
                            'id' => $volunteer->id,
                            'name' => $volunteer->name,
                            'email' => $volunteer->email,
                            'phone' => $volunteer->phone,
                            'motivation' => $volunteer->motivation ?? '',
                            'applied_date' => $volunteer->created_at,
                            'days_pending' => Carbon::parse($volunteer->created_at)->diffInDays(now()),
                        ];
                    })
                    ->toArray(),
                    
                'volunteer_statistics' => [
                    'total_applications' => Volunteers::count(),
                    'approval_rate' => $this->calculateApprovalRate(),
                    'average_response_time' => $this->getAverageResponseTime(),
                    'retention_rate' => $this->calculateVolunteerRetentionRate(),
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error getting volunteer management data', ['ajk_id' => $ajkId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get community engagement data
     */
    private function getCommunityEngagement(int $ajkId): array
    {
        try {
            return [
                'recent_messages' => ContactMessages::latest()
                    ->limit(10)
                    ->get()
                    ->map(function ($message) {
                        return [
                            'id' => $message->id,
                            'name' => $message->name,
                            'email' => $message->email,
                            'subject' => $message->subject ?? 'General Inquiry',
                            'status' => $message->status,
                            'received_date' => $message->created_at,
                            'priority' => $this->calculateMessagePriority($message),
                        ];
                    })
                    ->toArray(),
                    
                'engagement_metrics' => [
                    'total_inquiries' => ContactMessages::count(),
                    'response_rate' => $this->calculateResponseRate(),
                    'average_response_time' => $this->getAverageMessageResponseTime(),
                    'satisfaction_score' => $this->getCommunityFeedbackScore(),
                ],
                
                'outreach_activities' => [
                    'social_media_engagement' => $this->getSocialMediaMetrics(),
                    'community_events_organized' => $this->getCommunityEventsCount(),
                    'partnerships_established' => $this->getPartnershipsCount(),
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error getting community engagement data', ['ajk_id' => $ajkId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get asset overview data
     */
    private function getAssetOverview(int $ajkId): array
    {
        try {
            return [
                'asset_summary' => [
                    'total_value' => Asset::sum('value') ?? 0,
                    'total_items' => Asset::count(),
                    'categories' => Asset::select('category', DB::raw('count(*) as count'))
                        ->groupBy('category')
                        ->pluck('count', 'category')
                        ->toArray(),
                ],
                
                'recent_acquisitions' => Asset::latest()
                    ->limit(10)
                    ->get()
                    ->map(function ($asset) {
                        return [
                            'id' => $asset->id,
                            'name' => $asset->name,
                            'category' => $asset->category,
                            'value' => $asset->value,
                            'condition' => $asset->condition ?? 'good',
                            'acquired_date' => $asset->created_at,
                            'location' => $asset->location ?? 'Not specified',
                        ];
                    })
                    ->toArray(),
                    
                'maintenance_schedule' => $this->getMaintenanceSchedule(),
                'utilization_report' => $this->getAssetUtilizationReport(),
            ];
        } catch (Exception $e) {
            Log::error('Error getting asset overview', ['ajk_id' => $ajkId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get AJK quick actions
     */
    private function getAjkQuickActions(int $ajkId): array
    {
        try {
            $pendingVolunteers = Volunteers::where('status', 'pending')->count();
            $unreadMessages = ContactMessages::where('status', 'unread')->count();
            $upcomingEvents = Event::where('event_date', '>', now())
                ->where('event_date', '<=', now()->addDays(7))
                ->count();
            
            return [
                [
                    'title' => 'Review Applications',
                    'icon' => 'fas fa-user-check',
                    'url' => '/ajk/volunteers/pending',
                    'color' => 'warning',
                    'badge' => $pendingVolunteers > 0 ? $pendingVolunteers : null,
                ],
                [
                    'title' => 'Create Event',
                    'icon' => 'fas fa-calendar-plus',
                    'url' => '/ajk/events/create',
                    'color' => 'success',
                ],
                [
                    'title' => 'Manage Assets',
                    'icon' => 'fas fa-boxes',
                    'url' => '/ajk/assets',
                    'color' => 'info',
                ],
                [
                    'title' => 'Community Messages',
                    'icon' => 'fas fa-envelope',
                    'url' => '/ajk/messages',
                    'color' => 'primary',
                    'badge' => $unreadMessages > 0 ? $unreadMessages : null,
                ],
                [
                    'title' => 'Upcoming Events',
                    'icon' => 'fas fa-calendar-alt',
                    'url' => '/ajk/events/upcoming',
                    'color' => 'secondary',
                    'badge' => $upcomingEvents > 0 ? $upcomingEvents : null,
                ],
                [
                    'title' => 'Generate Reports',
                    'icon' => 'fas fa-chart-line',
                    'url' => '/ajk/reports',
                    'color' => 'danger',
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error getting AJK quick actions', ['ajk_id' => $ajkId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get event metrics
     */
    private function getEventMetrics(int $ajkId): array
    {
        try {
            return Event::with(['attendees'])
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'event_date' => $event->event_date,
                        'status' => $event->status ?? 'scheduled',
                        'expected_attendees' => $event->expected_attendees ?? 0,
                        'actual_attendees' => $event->attendees->count() ?? 0,
                        'success_rate' => $this->calculateEventSuccessRate($event),
                        'feedback_score' => $this->getEventFeedbackScore($event->id),
                        'cost' => $event->budget ?? 0,
                        'roi' => $this->calculateEventROI($event->id),
                    ];
                })
                ->toArray();
        } catch (Exception $e) {
            Log::error('Error getting event metrics', ['ajk_id' => $ajkId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get monthly overview
     */
    private function getMonthlyOverview(int $ajkId): array
    {
        try {
            $currentMonth = Carbon::now()->startOfMonth();
            
            return [
                'period' => $currentMonth->format('F Y'),
                'events_organized' => Event::where('event_date', '>=', $currentMonth)->count(),
                'volunteers_recruited' => Volunteers::where('created_at', '>=', $currentMonth)->count(),
                'messages_received' => ContactMessages::where('created_at', '>=', $currentMonth)->count(),
                'assets_acquired' => Asset::where('created_at', '>=', $currentMonth)->count(),
                'budget_utilization' => $this->calculateBudgetUtilization($currentMonth),
                'community_reach' => $this->calculateCommunityReach($currentMonth),
                'goals_progress' => $this->getGoalsProgress($currentMonth),
            ];
        } catch (Exception $e) {
            Log::error('Error getting monthly overview', ['ajk_id' => $ajkId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get centre coordination data
     */
    private function getCentreCoordination(int $ajkId): array
    {
        try {
            return Centres::with(['users', 'activities', 'trainees'])
                ->get()
                ->map(function ($centre) {
                    return [
                        'id' => $centre->id,
                        'name' => $centre->name,
                        'location' => $centre->location,
                        'status' => $centre->status,
                        'coordinator' => $this->getCentreCoordinator($centre->id),
                        'performance_score' => $this->calculateCentrePerformance($centre->id),
                        'support_needed' => $this->assessCentreSupport($centre->id),
                        'last_inspection' => $this->getLastInspectionDate($centre->id),
                        'compliance_status' => $this->getComplianceStatus($centre->id),
                    ];
                })
                ->toArray();
        } catch (Exception $e) {
            Log::error('Error getting centre coordination data', ['ajk_id' => $ajkId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    // Chart helper methods
    private function getEventParticipationChart(): array
    {
        // Implementation for event participation chart
        return [];
    }

    private function getVolunteerEngagementChart(): array
    {
        // Implementation for volunteer engagement chart
        return [];
    }

    private function getCommunityFeedbackChart(): array
    {
        // Implementation for community feedback chart
        return [];
    }

    private function getAssetUtilizationChart(): array
    {
        // Implementation for asset utilization chart
        return [];
    }

    private function getCentrePerformanceChart(): array
    {
        // Implementation for centre performance chart
        return [];
    }

    // Helper calculation methods
    private function getVolunteerParticipationRate(): float
    {
        // Implementation for volunteer participation rate
        return 0;
    }

    private function getCentreCoordinationScore(): float
    {
        // Implementation for centre coordination score
        return 0;
    }

    private function getCommunityFeedbackScore(): float
    {
        // Implementation for community feedback score
        return 0;
    }

    private function getVolunteerLastActivity(int $volunteerId): ?string
    {
        // Implementation for volunteer last activity
        return null;
    }

    private function getVolunteerEventCount(int $volunteerId): int
    {
        // Implementation for volunteer event count
        return 0;
    }

    private function calculateApprovalRate(): float
    {
        try {
            $total = Volunteers::count();
            $approved = Volunteers::where('status', 'approved')->count();
            return $total > 0 ? ($approved / $total) * 100 : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getAverageResponseTime(): float
    {
        // Implementation for average response time calculation
        return 0;
    }

    private function calculateVolunteerRetentionRate(): float
    {
        // Implementation for volunteer retention rate
        return 0;
    }

    private function calculateMessagePriority($message): string
    {
        // Implementation for message priority calculation
        return 'normal';
    }

    private function calculateResponseRate(): float
    {
        // Implementation for response rate calculation
        return 0;
    }

    private function getAverageMessageResponseTime(): float
    {
        // Implementation for average message response time
        return 0;
    }

    private function getSocialMediaMetrics(): array
    {
        // Implementation for social media metrics
        return [];
    }

    private function getCommunityEventsCount(): int
    {
        // Implementation for community events count
        return 0;
    }

    private function getPartnershipsCount(): int
    {
        // Implementation for partnerships count
        return 0;
    }

    private function getMaintenanceSchedule(): array
    {
        // Implementation for maintenance schedule
        return [];
    }

    private function getAssetUtilizationReport(): array
    {
        // Implementation for asset utilization report
        return [];
    }

    private function calculateEventSuccessRate($event): float
    {
        // Implementation for event success rate
        return 0;
    }

    private function getEventFeedbackScore(int $eventId): float
    {
        // Implementation for event feedback score
        return 0;
    }

    private function calculateEventROI(int $eventId): float
    {
        // Implementation for event ROI calculation
        return 0;
    }

    private function calculateBudgetUtilization(Carbon $month): float
    {
        // Implementation for budget utilization
        return 0;
    }

    private function calculateCommunityReach(Carbon $month): int
    {
        // Implementation for community reach calculation
        return 0;
    }

    private function getGoalsProgress(Carbon $month): array
    {
        // Implementation for goals progress
        return [];
    }

    private function getCentreCoordinator(int $centreId): ?string
    {
        // Implementation for centre coordinator lookup
        return null;
    }

    private function calculateCentrePerformance(int $centreId): float
    {
        // Implementation for centre performance calculation
        return 0;
    }

    private function assessCentreSupport(int $centreId): array
    {
        // Implementation for centre support assessment
        return [];
    }

    private function getLastInspectionDate(int $centreId): ?string
    {
        // Implementation for last inspection date
        return null;
    }

    private function getComplianceStatus(int $centreId): string
    {
        // Implementation for compliance status
        return 'compliant';
    }

    /**
     * Get fallback data when main query fails
     */
    private function getFallbackAjkData(int $ajkId): array
    {
        return [
            'stats' => [
                'total_events' => 0,
                'upcoming_events' => 0,
                'active_volunteers' => 0,
                'pending_volunteers' => 0,
                'total_assets' => 0,
                'asset_value' => 0,
                'community_messages' => 0,
                'unread_messages' => 0,
                'events_this_month' => 0,
                'volunteer_participation' => 0,
                'centre_coordination_score' => 0,
                'community_satisfaction' => 0,
            ],
            'charts' => [],
            'notifications' => [],
            'upcomingEvents' => [],
            'volunteerManagement' => [],
            'communityEngagement' => [],
            'assetOverview' => [],
            'quickActions' => $this->getAjkQuickActions($ajkId),
            'eventMetrics' => [],
            'monthlyOverview' => [],
            'centreCoordination' => [],
        ];
    }
}