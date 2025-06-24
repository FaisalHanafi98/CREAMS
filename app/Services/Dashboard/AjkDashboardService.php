<?php

namespace App\Services\Dashboard;

use App\Models\Users;
use App\Models\Events;
use App\Models\Volunteers;
use App\Models\ContactMessages;
use App\Models\Asset;
use App\Models\Activity;
use App\Models\Centres;
use App\Services\Asset\AssetManagementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Exception;

class AjkDashboardService extends BaseDashboardService
{
    private AssetManagementService $assetService;

    public function __construct(AssetManagementService $assetService)
    {
        $this->assetService = $assetService;
    }
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
                    'assetOverview' => $this->getEnhancedAssetOverview($ajkId),
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
            $eventsExist = Schema::hasTable('events');
            
            return [
                'total_events' => $eventsExist ? Events::count() : 0,
                'upcoming_events' => $eventsExist ? Events::where('event_date', '>', now())->count() : 0,
                'active_volunteers' => Volunteers::where('status', 'approved')->count(),
                'pending_volunteers' => Volunteers::where('status', 'pending')->count(),
                'total_assets' => Asset::count(),
                'asset_value' => 0, // Asset values not available in current table structure
                'community_messages' => ContactMessages::count(),
                'unread_messages' => ContactMessages::where('status', 'unread')->count(),
                'events_this_month' => $eventsExist ? Events::whereMonth('event_date', Carbon::now()->month)->count() : 0,
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
     * Get enhanced asset overview data using AssetManagementService
     */
    private function getEnhancedAssetOverview(int $ajkId): array
    {
        try {
            // Get comprehensive asset data from the service
            $assetData = $this->assetService->getDashboardData();
            
            return [
                'asset_summary' => [
                    'total_value' => $assetData['statistics']['total_value'] ?? 0,
                    'total_items' => $assetData['statistics']['total_assets'] ?? 0,
                    'available_assets' => $assetData['statistics']['available_assets'] ?? 0,
                    'in_use_assets' => $assetData['statistics']['in_use_assets'] ?? 0,
                    'maintenance_assets' => $assetData['statistics']['maintenance_assets'] ?? 0,
                    'utilization_rate' => $this->calculateUtilizationRate($assetData),
                ],
                
                'financial_overview' => [
                    'total_purchase_value' => $assetData['financial_metrics']['total_purchase_value'] ?? 0,
                    'total_current_value' => $assetData['financial_metrics']['total_current_value'] ?? 0,
                    'total_depreciation' => $assetData['financial_metrics']['total_depreciation'] ?? 0,
                    'depreciation_percentage' => $assetData['financial_metrics']['depreciation_percentage'] ?? 0,
                    'maintenance_cost_mtd' => $assetData['statistics']['maintenance_cost_mtd'] ?? 0,
                    'average_asset_value' => $assetData['financial_metrics']['average_asset_value'] ?? 0,
                ],
                
                'operational_metrics' => [
                    'maintenance_efficiency' => $this->getMaintenanceEfficiency(),
                    'asset_health_score' => $this->calculateAssetHealthScore($assetData),
                    'compliance_status' => $this->getComplianceStatus(),
                    'average_age_days' => $assetData['statistics']['average_age'] ?? 0,
                ],
                
                'alerts_and_actions' => [
                    'maintenance_overdue' => $assetData['maintenance_alerts']['overdue_count'] ?? 0,
                    'maintenance_upcoming' => $assetData['maintenance_alerts']['upcoming_count'] ?? 0,
                    'warranty_expiring' => $this->getWarrantyExpiringAssets(),
                    'replacement_due' => $this->getReplacementDueAssets(),
                ],
                
                'recent_activities' => [
                    'recent_movements' => array_slice($assetData['recent_movements'] ?? [], 0, 5),
                    'maintenance_completed' => $this->getRecentMaintenanceCompletions(),
                    'new_acquisitions' => $this->getRecentAcquisitions(),
                ],
                
                'distribution_charts' => [
                    'by_type' => $assetData['distribution']['by_type'] ?? [],
                    'by_location' => $assetData['distribution']['by_location'] ?? [],
                    'by_status' => $assetData['distribution']['by_status'] ?? [],
                ],
                
                'utilization_breakdown' => [
                    'overall_utilization' => $assetData['utilization_rates']['overall_utilization'] ?? 0,
                    'available_rate' => $assetData['utilization_rates']['available_rate'] ?? 0,
                    'maintenance_rate' => $assetData['utilization_rates']['maintenance_rate'] ?? 0,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error getting enhanced asset overview', [
                'ajk_id' => $ajkId, 
                'error' => $e->getMessage()
            ]);
            
            return $this->getFallbackAssetOverview();
        }
    }

    /**
     * Get asset overview data (fallback method)
     */
    private function getAssetOverview(int $ajkId): array
    {
        try {
            return [
                'asset_summary' => [
                    'total_value' => 0, // Asset values not available in current table structure
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
            $upcomingEvents = Schema::hasTable('events') 
                ? Events::where('event_date', '>', now())
                    ->where('event_date', '<=', now()->addDays(7))
                    ->count()
                : 0;
            
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
            if (!Schema::hasTable('events')) {
                return [];
            }
            
            return Events::with(['attendees'])
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
                'events_organized' => Schema::hasTable('events') ? Events::where('event_date', '>=', $currentMonth)->count() : 0,
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

    private function getComplianceStatus(int $centreId = null): string
    {
        // Implementation for compliance status
        return 'compliant';
    }

    // Enhanced asset management helper methods
    private function calculateUtilizationRate(array $assetData): float
    {
        $totalAssets = $assetData['statistics']['total_assets'] ?? 0;
        $inUseAssets = $assetData['statistics']['in_use_assets'] ?? 0;
        
        return $totalAssets > 0 ? ($inUseAssets / $totalAssets) * 100 : 0;
    }

    private function getMaintenanceEfficiency(): float
    {
        // Calculate maintenance efficiency based on completion rate and time
        try {
            // This would typically use AssetMaintenance model when available
            return 85.0; // Placeholder value
        } catch (Exception $e) {
            return 0;
        }
    }

    private function calculateAssetHealthScore(array $assetData): float
    {
        // Calculate overall asset health score
        try {
            $totalAssets = $assetData['statistics']['total_assets'] ?? 0;
            $maintenanceAssets = $assetData['statistics']['maintenance_assets'] ?? 0;
            $overdueCount = $assetData['maintenance_alerts']['overdue_count'] ?? 0;
            
            if ($totalAssets == 0) return 100;
            
            $maintenanceRate = ($maintenanceAssets / $totalAssets) * 100;
            $overdueRate = ($overdueCount / $totalAssets) * 100;
            
            // Lower maintenance and overdue rates = higher health score
            $healthScore = 100 - ($maintenanceRate * 0.5) - ($overdueRate * 2);
            
            return max(0, min(100, $healthScore));
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getWarrantyExpiringAssets(): int
    {
        try {
            // Count assets with warranty expiring in next 30 days
            return \App\Models\Asset::whereNotNull('warranty_date')
                ->whereBetween('warranty_date', [now(), now()->addDays(30)])
                ->count();
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getReplacementDueAssets(): int
    {
        try {
            // Count assets that are near end of life
            return \App\Models\Asset::whereNotNull('purchase_date')
                ->where('purchase_date', '<', now()->subYears(5))
                ->where('status', '!=', 'retired')
                ->count();
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getRecentMaintenanceCompletions(): array
    {
        try {
            // Get recent completed maintenance records
            return []; // Placeholder - would use AssetMaintenance model
        } catch (Exception $e) {
            return [];
        }
    }

    private function getRecentAcquisitions(): array
    {
        try {
            return \App\Models\Asset::latest()
                ->limit(5)
                ->get(['id', 'name', 'purchase_price', 'created_at'])
                ->map(function ($asset) {
                    return [
                        'id' => $asset->id,
                        'name' => $asset->name,
                        'value' => $asset->purchase_price ?? 0,
                        'date' => $asset->created_at,
                    ];
                })
                ->toArray();
        } catch (Exception $e) {
            return [];
        }
    }

    private function getFallbackAssetOverview(): array
    {
        return [
            'asset_summary' => [
                'total_value' => 0,
                'total_items' => 0,
                'available_assets' => 0,
                'in_use_assets' => 0,
                'maintenance_assets' => 0,
                'utilization_rate' => 0,
            ],
            'financial_overview' => [
                'total_purchase_value' => 0,
                'total_current_value' => 0,
                'total_depreciation' => 0,
                'depreciation_percentage' => 0,
                'maintenance_cost_mtd' => 0,
                'average_asset_value' => 0,
            ],
            'operational_metrics' => [
                'maintenance_efficiency' => 0,
                'asset_health_score' => 0,
                'compliance_status' => 'unknown',
                'average_age_days' => 0,
            ],
            'alerts_and_actions' => [
                'maintenance_overdue' => 0,
                'maintenance_upcoming' => 0,
                'warranty_expiring' => 0,
                'replacement_due' => 0,
            ],
            'recent_activities' => [
                'recent_movements' => [],
                'maintenance_completed' => [],
                'new_acquisitions' => [],
            ],
            'distribution_charts' => [
                'by_type' => [],
                'by_location' => [],
                'by_status' => [],
            ],
            'utilization_breakdown' => [
                'overall_utilization' => 0,
                'available_rate' => 0,
                'maintenance_rate' => 0,
            ],
        ];
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