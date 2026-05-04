<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\Centre;
use Illuminate\Support\Facades\Log;
use App\Helpers\EncryptionHelper;

class SearchController extends Controller
{
    /**
     * Handle the global search functionality
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        // Get the search query
        $query = $request->input('query');

        // Get user's centre ID from session, fetch from user record if not in session
        $centreId = session('centre_id');
        if (!$centreId) {
            $userId = session('id');
            $user = User::find($userId);
            if ($user && $user->centre_id) {
                $centreId = $user->centre_id;
                session(['centre_id' => $centreId]);
            } else {
                // Default to centre 01 if still not found
                $centreId = '01';
            }
        }

        // Log the search request
        Log::info('Global search initiated', [
            'query' => $query,
            'user_id' => session('id'),
            'role' => session('role'),
            'centre_id' => $centreId
        ]);

        // Initialize results array
        $results = [];

        try {
            // If query is empty, return empty results
            if (empty($query) || strlen($query) < 2) {
                return response()->json(['results' => $results]);
            }

            // Search for staffs/teachers in User model (centre-specific)
            $users = User::where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('iium_id', 'LIKE', "%{$query}%");
            })
            ->where('status', 'active')
            ->where('centre_id', $centreId)
            ->limit(5)
            ->get();

            // Format users results
            foreach ($users as $user) {
                // Get centre name
                $centreName = "Unknown";
                if ($user->centre_id) {
                    $centre = Centre::where('centre_id', $user->centre_id)->first();
                    if ($centre) {
                        $centreName = $centre->centre_name;
                    }
                }

                // Generate encrypted ID using the app's standard EncryptionHelper
                $encryptedId = EncryptionHelper::generateEncryptedId($user->id);

                $results[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'type' => 'Staff',
                    'role' => ucfirst($user->role),
                    'subtitle' => ucfirst($user->role) . ' • ' . $centreName,
                    'location' => $centreName,
                    'avatar' => $user->avatar ? asset('storage/avatars/' . $user->avatar) : asset('images/default-avatar.png'),
                    'url' => route('staffs.profile', ['encrypted_id' => $encryptedId])
                ];
            }

            // Search for trainees (centre-specific and active only)
            $trainees = Trainee::where(function ($q) use ($query) {
                $q->where('trainee_first_name', 'LIKE', "%{$query}%")
                  ->orWhere('trainee_last_name', 'LIKE', "%{$query}%")
                  ->orWhere('trainee_email', 'LIKE', "%{$query}%");
            })
            ->where('centre_id', $centreId)
            ->where('status', 'active')
            ->limit(5)
            ->get();

            // Search for activities (centre-specific and active only)
            $activities = Activity::where(function ($q) use ($query) {
                $q->where('activity_name', 'LIKE', "%{$query}%")
                  ->orWhere('activity_description', 'LIKE', "%{$query}%");
            })
            ->where('centre_id', $centreId)
            ->where('is_active', true)
            ->limit(5)
            ->get();

            // Format trainees results
            foreach ($trainees as $trainee) {
                // Get centre name
                $centreName = "Unknown";
                if ($trainee->centre_id) {
                    $centre = Centre::where('centre_id', $trainee->centre_id)->first();
                    if ($centre) {
                        $centreName = $centre->centre_name;
                    }
                }

                // Generate encrypted ID using the app's standard EncryptionHelper
                $encryptedId = EncryptionHelper::generateEncryptedId($trainee->id);

                $disability = $trainee->trainee_condition ?? 'No condition specified';

                $results[] = [
                    'id' => $trainee->id,
                    'name' => $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name,
                    'type' => 'Trainee',
                    'subtitle' => $disability . ' • ' . $centreName,
                    'location' => $centreName,
                    'avatar' => asset('images/default-avatar.png'),
                    'url' => route('traineeprofile', ['encrypted_id' => $encryptedId])
                ];
            }

            // Format activities results
            foreach ($activities as $activity) {
                // Get instructor name
                $instructorName = 'No instructor';
                if ($activity->teacher_id) {
                    $instructor = User::find($activity->teacher_id);
                    if ($instructor) {
                        $instructorName = $instructor->name;
                    }
                }

                // Get centre name
                $centreName = "Unknown";
                if ($activity->centre_id) {
                    $centre = Centre::where('centre_id', $activity->centre_id)->first();
                    if ($centre) {
                        $centreName = $centre->centre_name;
                    }
                }

                $results[] = [
                    'id' => $activity->id,
                    'name' => $activity->activity_name,
                    'type' => 'Activity',
                    'subtitle' => $instructorName . ' • ' . $centreName,
                    'location' => $centreName,
                    'avatar' => asset('images/activity-icon.png'),
                    'url' => route('activities.show', ['id' => $activity->id])
                ];
            }

            // Sort results by name
            usort($results, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            // Limit to max 10 results total
            $results = array_slice($results, 0, 10);

            // Log search results count
            Log::info('Search results generated', [
                'query' => $query,
                'count' => count($results)
            ]);

            // Return results as JSON
            return response()->json(['results' => $results]);

        } catch (\Exception $e) {
            // Log error with detailed information
            Log::error('Error during global search', [
                'query' => $query,
                'centre_id' => $centreId,
                'user_id' => session('id'),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return detailed error for debugging
            return response()->json([
                'results' => [],
                'error' => 'Search error: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Display the search page for direct navigation
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = $request->input('query', '');

        // If query is empty, redirect to dashboard
        if (empty($query)) {
            return redirect()->route('dashboard');
        }

        // Return the search view (you can create a dedicated search results page if needed)
        return view('search', [
            'query' => $query
        ]);
    }
}
