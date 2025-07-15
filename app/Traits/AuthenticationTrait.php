<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait AuthenticationTrait
{
    /**
     * Get the user's role based on the model class
     *
     * @return string
     */
    public function getRole()
    {
        // Get the fully qualified class name
        $className = get_class($this);
        
        // Log the class name for debugging
        Log::debug('Determining role from class', ['class' => $className]);
        
        // Map class names to roles using direct string matching
        if (strpos($className, 'Admins') !== false) {
            return 'admin';
        } elseif (strpos($className, 'Supervisors') !== false) {
            return 'supervisor';
        } elseif (strpos($className, 'Teachers') !== false) {
            return 'teacher';
        } elseif (strpos($className, 'AJKs') !== false) {
            return 'ajk';
        }
        
        // Default fallback - extract from class basename
        $baseName = class_basename($className);
        $role = strtolower(rtrim($baseName, 's'));
        
        Log::debug('Determined role via fallback', ['role' => $role]);
        
        return $role;
    }

    /**
     * Get role hierarchy level with enhanced flexibility
     *
     * @return int
     */
    public function getRoleLevel(): int
    {
        $roleLevels = [
            'admin' => 5,
            'supervisor' => 4,
            'teacher' => 3,
            'ajk' => 2,
            'unknown' => 1
        ];

        $role = $this->getRole();

        // Log any roles not in the predefined levels
        if (!isset($roleLevels[$role])) {
            Log::warning('Undefined role level', [
                'role' => $role,
                'class' => static::class
            ]);
        }

        return $roleLevels[$role] ?? 0;
    }

    /**
     * Get accessible modules with more granular control
     *
     * @return array
     */
    public function getAccessibleModules(): array
    {
        $moduleAccess = [
            'admin' => [
                'dashboard' => true,
                'users' => [
                    'view' => true,
                    'create' => true,
                    'edit' => true,
                    'delete' => true
                ],
                'trainees' => [
                    'view' => true,
                    'create' => true,
                    'edit' => true,
                    'delete' => true
                ],
                'activities' => [
                    'view' => true,
                    'create' => true,
                    'edit' => true,
                    'delete' => true
                ],
                'reports' => [
                    'view' => true,
                    'generate' => true
                ],
                'settings' => [
                    'view' => true,
                    'edit' => true
                ],
                'assets' => [
                    'view' => true,
                    'manage' => true
                ]
            ],
            'supervisor' => [
                'dashboard' => true,
                'trainees' => [
                    'view' => true,
                    'create' => true,
                    'edit' => true
                ],
                'teachers' => [
                    'view' => true
                ],
                'activities' => [
                    'view' => true,
                    'create' => true
                ],
                'reports' => [
                    'view' => true,
                    'limited' => true
                ]
            ],
            'teacher' => [
                'dashboard' => true,
                'trainees' => [
                    'view' => true,
                    'edit' => ['own' => true]
                ],
                'classes' => [
                    'view' => true,
                    'manage' => ['own' => true]
                ],
                'activities' => [
                    'view' => true,
                    'create' => ['own' => true]
                ]
            ],
            'ajk' => [
                'dashboard' => true,
                'reports' => [
                    'view' => true,
                    'limited' => true
                ],
                'activities' => [
                    'view' => true
                ]
            ]
        ];

        $role = $this->getRole();

        // Log any roles not in the predefined modules
        if (!isset($moduleAccess[$role])) {
            Log::warning('Undefined module access', [
                'role' => $role,
                'class' => static::class
            ]);
        }

        return $moduleAccess[$role] ?? [];
    }

    /**
     * Check if user can access a specific module with enhanced permission checking
     *
     * @param string $module
     * @param string|null $permission
     * @param array|string|null $context Additional context for permission checking
     * @return bool
     */
    public function canAccessModule(
        string $module, 
        ?string $permission = null, 
        $context = null
    ): bool {
        $accessibleModules = $this->getAccessibleModules();

        // Check if module exists
        if (!isset($accessibleModules[$module])) {
            Log::warning('Attempted access to undefined module', [
                'module' => $module,
                'role' => $this->getRole(),
                'class' => static::class
            ]);
            return false;
        }

        $modulePermissions = $accessibleModules[$module];

        // No specific permission check - basic module access
        if ($permission === null) {
            return $modulePermissions === true;
        }

        // Detailed permission check
        if (!isset($modulePermissions[$permission])) {
            Log::warning('Attempted access to undefined module permission', [
                'module' => $module,
                'permission' => $permission,
                'role' => $this->getRole(),
                'class' => static::class
            ]);
            return false;
        }

        $permissionValue = $modulePermissions[$permission];

        // Boolean permission
        if (is_bool($permissionValue)) {
            return $permissionValue;
        }

        // Context-based permission
        if (is_array($permissionValue)) {
            // Check for 'own' or other context-specific permissions
            if (isset($permissionValue['own']) && $context === 'own') {
                return true;
            }

            if (isset($permissionValue['limited']) && $context === 'limited') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has a minimum role level with logging
     *
     * @param string $requiredRole
     * @return bool
     */
    public function hasMinimumRole(string $requiredRole): bool
    {
        $roleLevels = [
            'admin' => 5,
            'supervisor' => 4,
            'teacher' => 3,
            'ajk' => 2,
            'unknown' => 1
        ];

        $currentRole = $this->getRole();

        // Log any undefined roles
        if (!isset($roleLevels[$currentRole]) || !isset($roleLevels[$requiredRole])) {
            Log::warning('Undefined role in minimum role check', [
                'currentRole' => $currentRole,
                'requiredRole' => $requiredRole,
                'class' => static::class
            ]);
        }

        return isset($roleLevels[$currentRole]) && 
               isset($roleLevels[$requiredRole]) && 
               $roleLevels[$currentRole] >= $roleLevels[$requiredRole];
    }
}