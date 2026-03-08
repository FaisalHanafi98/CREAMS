<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Centre;
use App\Traits\HandlesEncryptedIds;

class StaffsHomeController extends Controller
{
    use HandlesEncryptedIds;
    /**
     * Display the main teachers/staff home page with filtering capabilities
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            // Get query parameters for filtering
            $roleFilter = $request->query('role');
            $educationLevelFilter = $request->query('education_level');
            $specialisationFilter = $request->query('specialisation');
            $centreFilter = $request->query('centre');
            $searchTerm = $request->query('search');
            
            // Fix N+1 query: Use Eloquent relationships instead of manual joins
            $query = User::with(['centre'])->select(
                'staffs.id', 
                'staffs.name as user_name', 
                'staffs.role', 
                'staffs.education_level', 
                'staffs.education_specialization', 
                'staffs.teaching_specialization',
                'staffs.position',
                'staffs.avatar',
                'staffs.centre_id',
                'staffs.email',
                'staffs.status',
                'staffs.created_at'
            );
            
            // Apply filters if provided
            if ($roleFilter) {
                $query->where('staffs.role', $roleFilter);
            }
            
            if ($educationLevelFilter) {
                $query->where('staffs.education_level', $educationLevelFilter);
            }
            
            if ($specialisationFilter) {
                $query->where(function($q) use ($specialisationFilter) {
                    $q->where('staffs.education_specialization', 'like', "%{$specialisationFilter}%")
                      ->orWhere('staffs.teaching_specialization', 'like', "%{$specialisationFilter}%");
                });
            }
            
            if ($centreFilter) {
                $query->where('staffs.centre_id', $centreFilter);
            }
            
            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('staffs.name', 'like', "%{$searchTerm}%")
                      ->orWhere('staffs.email', 'like', "%{$searchTerm}%")
                      ->orWhere('staffs.education_level', 'like', "%{$searchTerm}%")
                      ->orWhere('staffs.education_specialization', 'like', "%{$searchTerm}%")
                      ->orWhere('staffs.teaching_specialization', 'like', "%{$searchTerm}%")
                      ->orWhere('staffs.position', 'like', "%{$searchTerm}%");
                });
            }
            
            // Only get active users
            $query->where('staffs.status', 'active');
            
            // Order by role, then education level
            $query->orderBy('staffs.role', 'asc')
                  ->orderBy('staffs.education_level', 'asc');
            
            // Get the data with pagination
            $users = $query->paginate(8);
            
            // Transform paginated items
            $users->getCollection()->transform(function($user) {
                if (empty($user->education_level)) {
                    $user->education_level = 'Not Specified';
                }
                
                // Centre name is now included from the join, but handle null cases
                if (empty($user->centre_name)) {
                    $user->centre_name = 'Not Assigned';
                }
                
                // Add encrypted ID for secure URL generation
                $user->encrypted_id = $this->generateEncryptedId($user->id);
                
                return $user;
            });
            
            // For filter dropdown values, we need to get all users (not paginated)
            $allUsersForFilters = User::with(['centre'])->where('status', 'active')->get();
            
            // Get distinct values for filters
            $educationLevels = $allUsersForFilters->pluck('education_level')->unique()->filter()->sort()->values();
            $teachingSpecializations = $allUsersForFilters->pluck('teaching_specialization')->unique()->filter()->sort()->values();
            $educationSpecializations = $allUsersForFilters->pluck('education_specialization')->unique()->filter()->sort()->values();
            $roles = $allUsersForFilters->pluck('role')->unique()->sort()->values();
            
            // Combine specializations for backwards compatibility
            $specialisations = $teachingSpecializations->merge($educationSpecializations)
                ->unique()->filter()->sort()->values();
            
            // Get centres for filter dropdown
            try {
                $centres = Centre::where('centres.status', 'active')
                    ->get(['centre_id', 'centre_name']);
            } catch (\Exception $e) {
                // If status column doesn't exist, get all centres
                try {
                    $centres = Centre::all(['centre_id', 'centre_name']);
                } catch (\Exception $e2) {
                    $centres = collect(); // Empty collection if error
                    Log::error('Error fetching centres: ' . $e2->getMessage());
                }
            }
            
            // Statistics for dashboard (based on all users, not just current page)
            $stats = [
                'total_users' => $allUsersForFilters->count(),
                'teachers_count' => $allUsersForFilters->where('role', 'teacher')->count(),
                'supervisors_count' => $allUsersForFilters->where('role', 'supervisor')->count(),
                'admins_count' => $allUsersForFilters->where('role', 'admin')->count(),
                'ajks_count' => $allUsersForFilters->where('role', 'ajk')->count(),
                'education_breakdown' => $allUsersForFilters->groupBy('education_level')
                    ->map(function ($group) {
                        return $group->count();
                    })->toArray(),
                'centre_breakdown' => $allUsersForFilters->groupBy(function($user) {
                    return $user->centre ? $user->centre->centre_name : 'Not Assigned';
                })->map(function ($group) {
                    return $group->count();
                })->toArray(),
            ];
            
            // Get current user's role for permission checks in the view
            $currentUserRole = session('role') ?? 'teacher';
            
            // Return view with data and filters
            return view('staff.home', [
                'users' => $users,
                'educationLevels' => $educationLevels,
                'teachingSpecializations' => $teachingSpecializations,
                'educationSpecializations' => $educationSpecializations,
                'specialisations' => $specialisations, // Add this for backwards compatibility
                'roles' => $roles,
                'centres' => $centres,
                'stats' => $stats,
                'currentUserRole' => $currentUserRole,
                'filters' => [
                    'role' => $roleFilter,
                    'education_level' => $educationLevelFilter,
                    'specialisation' => $specialisationFilter,
                    'centre' => $centreFilter,
                    'search' => $searchTerm,
                ]
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error in TeachersHomeController@index: ' . $e->getMessage());
            
            // Return a fallback view with error message
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(), // Empty collection
                0, // Total
                8, // Per page
                1, // Current page
                ['path' => request()->url()]
            );
            
            return view('staff.home', [
                'error' => 'An error occurred while loading the staff list. Please try again later.',
                'users' => $emptyPaginator, // Empty paginator
                'educationLevels' => collect(),
                'teachingSpecializations' => collect(),
                'educationSpecializations' => collect(),
                'specialisations' => collect(),
                'roles' => collect(),
                'centres' => collect(),
                'stats' => [],
                'currentUserRole' => session('role') ?? 'teacher',
                'filters' => []
            ]);
        }
    }

    /**
     * Display the staff creation/registration page
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            // Get active centres for dropdown
            $centres = Centre::where('centre_status', 'active')
                ->get(['centre_id', 'centre_name']);

            return view('auth.register', compact('centres'));
        } catch (\Exception $e) {
            Log::error('Error loading staff creation page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('staffs.home')
                ->with('error', 'Unable to load staff creation page');
        }
    }

    /**
     * Display the user profile update page
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function updateuserpage(Request $request, $id)
    {
        try {
            // Debug: Log what ID we're trying to fetch
            Log::info('TeachersHomeController@updateuserpage called', [
                'requested_id' => $id,
                'session_id' => session('id'),
                'current_user' => session('name')
            ]);
            
            // Get user with ID
            $user = User::findOrFail($id);
            
            // Debug: Log what user we found
            Log::info('User found for profile viewing', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email
            ]);
            
            // Get centres for dropdown
            try {
                $centres = Centre::where('centres.status', 'active')
                    ->get(['centre_id', 'centre_name']);
            } catch (\Exception $e) {
                // If status column doesn't exist, get all centres
                $centres = Centre::all(['centre_id', 'centre_name']);
            }
            
            // Check if current user has permission to edit this user
            $canEdit = $this->checkEditPermission($user);
            
            // Get current user's role for permission checks in the view
            $currentUserRole = session('role') ?? 'teacher';
            
            // Convert user to array for consistent access in view
            $userData = $user->toArray();
            $userData['role'] = $user->role;
            
            // Debug: Log the data being passed to view
            Log::info('Data being passed to profile view', [
                'user_data' => [
                    'id' => $userData['id'] ?? 'missing',
                    'name' => $userData['name'] ?? 'missing',
                    'email' => $userData['email'] ?? 'missing',
                    'role' => $userData['role'] ?? 'missing'
                ]
            ]);
            
            // Return view with data (using profile view)
            return view('profile', [
                'user' => $userData,  // Pass as array for consistent access
                'role' => $user->role,  // Add missing $role variable
                'name' => $user->name,  // Add missing $name variable
                'centres' => $centres,
                'canEdit' => $canEdit,
                'currentUserRole' => $currentUserRole
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error in TeachersHomeController@updateuserpage: ' . $e->getMessage());
            
            // Redirect back with error message
            return redirect()->route('teachershome')
                ->with('error', 'Unable to find user with ID: ' . $id);
        }
    }
    
    /**
     * Update user profile information
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateuser(Request $request, $id)
    {
        try {
            // Get user with ID
            $user = User::findOrFail($id);
            
            // Check if current user has permission to edit this user
            if (!$this->checkEditPermission($user)) {
                return redirect()->route('teachershome')
                    ->with('error', 'You do not have permission to edit this user.');
            }
            
            // Validate request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'education_level' => 'nullable|in:Diploma,Bachelor\'s,Master\'s,PhD',
                'education_specialization' => 'nullable|string|max:255',
                'teaching_specialization' => 'nullable|string|max:255',
                'position' => 'nullable|string|max:255',
                'centre_id' => 'nullable|string|exists:centres,centre_id',
                'status' => 'nullable|in:active,inactive',
            ]);
            
            // Update user with validated data
            $user->update($validatedData);
            
            // Redirect back with success message
            return redirect()->route('updateuser', ['id' => $id])
                ->with('success', 'User profile updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Redirect back with validation errors
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error in TeachersHomeController@updateuser: ' . $e->getMessage());
            
            // Redirect back with error message
            return redirect()->back()
                ->with('error', 'An error occurred while updating the user profile. Please try again later.')
                ->withInput();
        }
    }
    
    /**
     * Filter staff list with AJAX
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filter(Request $request)
    {
        try {
            // Get filter parameters
            $roleFilter = $request->input('role');
            $educationLevelFilter = $request->input('education_level');
            $specialisationFilter = $request->input('specialisation');
            $centreFilter = $request->input('centre');
            $searchTerm = $request->input('search');
            
            // Start with base query and include centre information
            $query = User::select(
                'staffs.id', 
                'staffs.name as user_name', 
                'staffs.role', 
                'staffs.education_level', 
                'staffs.education_specialization', 
                'staffs.teaching_specialization',
                'staffs.position',
                'staffs.avatar',
                'staffs.centre_id',
                'staffs.status',
                'centres.centre_name'
            )->leftJoin('centres', 'staffs.centre_id', '=', 'centres.centre_id');
            
            // Apply filters if provided
            if ($roleFilter) {
                $query->where('staffs.role', $roleFilter);
            }
            
            if ($educationLevelFilter) {
                $query->where('staffs.education_level', $educationLevelFilter);
            }
            
            if ($specialisationFilter) {
                $query->where(function($q) use ($specialisationFilter) {
                    $q->where('staffs.education_specialization', 'like', "%{$specialisationFilter}%")
                      ->orWhere('staffs.teaching_specialization', 'like', "%{$specialisationFilter}%");
                });
            }
            
            if ($centreFilter) {
                $query->where('staffs.centre_id', $centreFilter);
            }
            
            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('staffs.name', 'like', "%{$searchTerm}%")
                      ->orWhere('staffs.email', 'like', "%{$searchTerm}%")
                      ->orWhere('staffs.education_level', 'like', "%{$searchTerm}%")
                      ->orWhere('staffs.education_specialization', 'like', "%{$searchTerm}%")
                      ->orWhere('staffs.teaching_specialization', 'like', "%{$searchTerm}%")
                      ->orWhere('staffs.position', 'like', "%{$searchTerm}%");
                });
            }
            
            // Only get active users
            $query->where('staffs.status', 'active');
            
            // Order by role, then education level
            $query->orderBy('staffs.role', 'asc')
                  ->orderBy('staffs.education_level', 'asc');
            
            // Get the data
            $users = $query->get();
            
            // Map users to include default education info and handle null centre names
            $users = $users->map(function($user) {
                if (empty($user->education_level)) {
                    $user->education_level = 'Not Specified';
                }
                
                // Centre name is now included from the join, but handle null cases
                if (empty($user->centre_name)) {
                    $user->centre_name = 'Not Assigned';
                }
                
                return $user;
            });
            
            // Return JSON response
            return response()->json([
                'success' => true,
                'users' => $users,
                'count' => $users->count(),
                'filters' => [
                    'role' => $roleFilter,
                    'education_level' => $educationLevelFilter,
                    'specialisation' => $specialisationFilter,
                    'centre' => $centreFilter,
                    'search' => $searchTerm,
                ]
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error in TeachersHomeController@filter: ' . $e->getMessage());
            
            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while filtering the staff list.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check if current user has permission to edit the target user
     *
     * @param User $targetUser
     * @return bool
     */
    private function checkEditPermission($targetUser)
    {
        $currentUserRole = session('role') ?? 'teacher';
        $targetUserRole = $targetUser->role;
        
        // Role hierarchy for permission check
        $roleHierarchy = [
            'admin' => 4,
            'supervisor' => 3,
            'ajk' => 2,
            'teacher' => 1
        ];
        
        // Get hierarchy levels
        $currentUserLevel = $roleHierarchy[$currentUserRole] ?? 0;
        $targetUserLevel = $roleHierarchy[$targetUserRole] ?? 0;
        
        // User can only edit users with lower or equal hierarchy level
        return $currentUserLevel >= $targetUserLevel;
    }
}