<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Trainees;
use App\Models\Centres;
use Illuminate\Support\Facades\Log;

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

        // Log the search request
        Log::info('Global search initiated', [
            'query' => $query,
            'user_id' => session('id'),
            'role' => session('role')
        ]);

        // Initialize results array
        $results = [];

        try {
            // If query is empty, return empty results
            if (empty($query) || strlen($query) < 2) {
                return response()->json(['results' => $results]);
            }

            // Search for staffs/teachers in Users model
            $users = Users::where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('iium_id', 'LIKE', "%{$query}%");
            })
            ->where('status', 'active')
            ->limit(5)
            ->get();

            // Format users results
            foreach ($users as $user) {
                // Get centre name
                $centreName = "Unknown";
                if ($user->centre_id) {
                    $centre = Centres::where('centre_id', $user->centre_id)->first();
                    if ($centre) {
                        $centreName = $centre->centre_name;
                    }
                }

                $results[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'type' => ucfirst($user->role),
                    'location' => $centreName,
                    'avatar' => $user->avatar ? asset('storage/avatars/' . $user->avatar) : asset('images/default-avatar.png'),
                    'url' => route($user->role . '.user.view', ['id' => $user->id])
                ];
            }

            // Search for trainees
            $trainees = Trainees::where(function ($q) use ($query) {
                $q->where('trainee_first_name', 'LIKE', "%{$query}%")
                  ->orWhere('trainee_last_name', 'LIKE', "%{$query}%")
                  ->orWhere('trainee_email', 'LIKE', "%{$query}%");
            })
            ->limit(5)
            ->get();

            // Format trainees results
            foreach ($trainees as $trainee) {
                $results[] = [
                    'id' => $trainee->id,
                    'name' => $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name,
                    'type' => 'Trainee',
                    'location' => $trainee->centre_name,
                    'avatar' => $trainee->avatar ? asset('storage/trainee_avatars/' . $trainee->avatar) : asset('images/default-avatar.png'),
                    'url' => route('traineeprofile', ['id' => $trainee->id])
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
            // Log error
            Log::error('Error during global search', [
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return empty results on error
            return response()->json(['results' => [], 'error' => 'An error occurred while searching']);
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
