<?php

namespace App\Services\Dashboard;

use App\Services\Asset\AssetManagementService;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use Exception;

class DashboardServiceFactory
{
    private AssetManagementService $assetService;

    public function __construct(AssetManagementService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * Create dashboard service based on user role
     */
    public function make(string $role): BaseDashboardService
    {
        try {
            switch (strtolower($role)) {
                case 'admin':
                    return new AdminDashboardService();
                
                case 'supervisor':
                    return new SupervisorDashboardService();
                
                case 'teacher':
                    return new TeacherDashboardService();
                
                case 'ajk':
                    return new AjkDashboardService($this->assetService);
                
                default:
                    Log::warning('Unknown role requested for dashboard service', ['role' => $role]);
                    throw new InvalidArgumentException("Unknown role: {$role}");
            }
        } catch (Exception $e) {
            Log::error('Error creating dashboard service', [
                'role' => $role,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get available dashboard roles
     */
    public function getAvailableRoles(): array
    {
        return ['admin', 'supervisor', 'teacher', 'ajk'];
    }

    /**
     * Check if role is supported
     */
    public function isRoleSupported(string $role): bool
    {
        return in_array(strtolower($role), $this->getAvailableRoles());
    }

    /**
     * Get service class name for a role
     */
    public function getServiceClassName(string $role): string
    {
        switch (strtolower($role)) {
            case 'admin':
                return AdminDashboardService::class;
            case 'supervisor':
                return SupervisorDashboardService::class;
            case 'teacher':
                return TeacherDashboardService::class;
            case 'ajk':
                return AjkDashboardService::class;
            default:
                throw new InvalidArgumentException("Unknown role: {$role}");
        }
    }
}