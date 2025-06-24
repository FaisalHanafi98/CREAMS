<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trainees;
use App\Models\Centres;
use App\Models\Activities;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TraineeController extends Controller
{
    /**
     * Display a listing of all trainees.
     *
     * @return \Illuminate\View\View
     */
        // In TraineeController.php
    public function index()
    {
        // Get user role
        $role = session('role');
        Log::info('Trainees index accessed', [
            'user_id' => session('id'),
            'role' => $role
        ]);
        
        // Get trainees data
        $trainees = Trainees::all();
        $traineesByCenter = $trainees->groupBy('centre_name');
        $centres = Centres::where('status', 'active')->get();
        $totalTrainees = $trainees->count();
        $conditionTypes = $trainees->pluck('trainee_condition')->unique()->count();
        $newTraineesCount = $trainees->where('created_at', '>=', now()->subDays(30))->count();
        
        // Return the home view instead of index view
        return view('trainees.home', [
            'trainees' => $trainees,
            'traineesByCenter' => $traineesByCenter,
            'centres' => $centres,
            'totalTrainees' => $totalTrainees,
            'conditionTypes' => $conditionTypes,
            'newTraineesCount' => $newTraineesCount
        ]);
    }

    /**
     * Show the form for creating a new trainee.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $centers = Centers::where('status', 'active')->get();
        
        return view('trainees.create', [
            'centers' => $centers
        ]);
    }

    /**
     * Store a newly created trainee in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'trainee_first_name' => 'required|string|max:255',
            'trainee_last_name' => 'required|string|max:255',
            'trainee_email' => 'required|email|unique:trainees,trainee_email',
            'trainee_phone_number' => 'required|string|max:20',
            'trainee_date_of_birth' => 'required|date',
            'trainee_avatar' => 'nullable|image|max:2048',
            'centre_name' => 'required|string|max:255',
            'trainee_condition' => 'required|string|max:255',
        ]);
        
        $trainee = new Trainees();
        $trainee->trainee_first_name = $validatedData['trainee_first_name'];
        $trainee->trainee_last_name = $validatedData['trainee_last_name'];
        $trainee->trainee_email = $validatedData['trainee_email'];
        $trainee->trainee_phone_number = $validatedData['trainee_phone_number'];
        $trainee->trainee_date_of_birth = $validatedData['trainee_date_of_birth'];
        $trainee->centre_name = $validatedData['centre_name'];
        $trainee->trainee_condition = $validatedData['trainee_condition'];
        $trainee->trainee_attendance = 0; // Default attendance value
        
        // Handle avatar upload
        if ($request->hasFile('trainee_avatar')) {
            $avatar = $request->file('trainee_avatar');
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            $avatarPath = $avatar->storeAs('trainee_avatars', $avatarName, 'public');
            $trainee->trainee_avatar = 'storage/' . $avatarPath;
        } else {
            // Set default avatar
            $trainee->trainee_avatar = 'images/default-avatar.jpg';
        }
        
        $trainee->save();
        
        Log::info('Trainee created successfully', [
            'user_id' => session('id'),
            'trainee_id' => $trainee->id
        ]);
        
        return redirect()->route('trainees.index')
            ->with('success', 'Trainee registered successfully!');
    }

    /**
     * Display the specified trainee profile.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $trainee = Trainees::findOrFail($id);
        $activities = Activities::where('trainee_id', $id)
                              ->orderBy('activity_date', 'desc')
                              ->get();
        
        return view('trainees.show', [
            'trainee' => $trainee,
            'activities' => $activities
        ]);
    }

    /**
     * Show the form for editing the specified trainee.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $trainee = Trainees::findOrFail($id);
        $centers = Centers::where('status', 'active')->get();
        
        return view('trainees.edit', [
            'trainee' => $trainee,
            'centers' => $centers
        ]);
    }

    /**
     * Update the specified trainee in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $trainee = Trainees::findOrFail($id);
        
        // Validate input
        $validatedData = $request->validate([
            'trainee_first_name' => 'required|string|max:255',
            'trainee_last_name' => 'required|string|max:255',
            'trainee_email' => [
                'required',
                'email',
                Rule::unique('trainees', 'trainee_email')->ignore($id)
            ],
            'trainee_phone_number' => 'required|string|max:20',
            'trainee_date_of_birth' => 'required|date',
            'trainee_avatar' => 'nullable|image|max:2048',
            'centre_name' => 'required|string|max:255',
            'trainee_condition' => 'required|string|max:255',
        ]);
        
        $trainee->trainee_first_name = $validatedData['trainee_first_name'];
        $trainee->trainee_last_name = $validatedData['trainee_last_name'];
        $trainee->trainee_email = $validatedData['trainee_email'];
        $trainee->trainee_phone_number = $validatedData['trainee_phone_number'];
        $trainee->trainee_date_of_birth = $validatedData['trainee_date_of_birth'];
        $trainee->centre_name = $validatedData['centre_name'];
        $trainee->trainee_condition = $validatedData['trainee_condition'];
        
        // Handle avatar upload
        if ($request->hasFile('trainee_avatar')) {
            // Remove old avatar if exists and not default
            if ($trainee->trainee_avatar && 
                !str_contains($trainee->trainee_avatar, 'default-avatar') && 
                Storage::disk('public')->exists(str_replace('storage/', '', $trainee->trainee_avatar))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $trainee->trainee_avatar));
            }
            
            $avatar = $request->file('trainee_avatar');
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            $avatarPath = $avatar->storeAs('trainee_avatars', $avatarName, 'public');
            $trainee->trainee_avatar = 'storage/' . $avatarPath;
        }
        
        $trainee->save();
        
        Log::info('Trainee updated successfully', [
            'user_id' => session('id'),
            'trainee_id' => $trainee->id
        ]);
        
        return redirect()->route('trainees.show', $trainee->id)
            ->with('success', 'Trainee profile updated successfully!');
    }

    /**
     * Remove the specified trainee from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $trainee = Trainees::findOrFail($id);
        
        // Remove avatar file if exists and not the default
        if ($trainee->trainee_avatar && 
            !str_contains($trainee->trainee_avatar, 'default-avatar') && 
            Storage::disk('public')->exists(str_replace('storage/', '', $trainee->trainee_avatar))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $trainee->trainee_avatar));
        }
        
        // Delete related activities
        Activities::where('trainee_id', $id)->delete();
        
        // Delete the trainee
        $trainee->delete();
        
        Log::info('Trainee deleted successfully', [
            'user_id' => session('id'),
            'trainee_id' => $id
        ]);
        
        return redirect()->route('trainees.index')
            ->with('success', 'Trainee deleted successfully!');
    }
    
    /**
     * Display trainees by center.
     *
     * @return \Illuminate\View\View
     */
    public function byCenter()
    {
        $trainees = Trainees::all();
        $traineesByCenter = $trainees->groupBy('centre_name');
        $centers = Centers::where('status', 'active')->get();
        
        return view('trainees.by-center', [
            'traineesByCenter' => $traineesByCenter,
            'centers' => $centers
        ]);
    }
    
    /**
     * Search trainees by criteria.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $query = Trainees::query();
        
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function($q) use ($keyword) {
                $q->where('trainee_first_name', 'like', "%{$keyword}%")
                  ->orWhere('trainee_last_name', 'like', "%{$keyword}%")
                  ->orWhere('trainee_email', 'like', "%{$keyword}%");
            });
        }
        
        if ($request->filled('center')) {
            $query->where('centre_name', $request->input('center'));
        }
        
        if ($request->filled('condition')) {
            $query->where('trainee_condition', $request->input('condition'));
        }
        
        $trainees = $query->get();
        $centers = Centers::where('status', 'active')->get();
        $conditions = Trainees::select('trainee_condition')->distinct()->pluck('trainee_condition');
        
        return view('trainees.search', [
            'trainees' => $trainees,
            'centers' => $centers,
            'conditions' => $conditions,
            'searchParams' => $request->all()
        ]);
    }
    
    /**
     * Export trainees data to CSV.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        $trainees = Trainees::all();
        $fileName = 'trainees_' . Carbon::now()->format('Y-m-d') . '.csv';
        
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        
        $columns = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Birth Date', 'Age', 'Center', 'Condition'];
        
        $callback = function() use($trainees, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($trainees as $trainee) {
                $age = Carbon::parse($trainee->trainee_date_of_birth)->age;
                
                $row = [
                    $trainee->id,
                    $trainee->trainee_first_name,
                    $trainee->trainee_last_name,
                    $trainee->trainee_email,
                    $trainee->trainee_phone_number,
                    $trainee->trainee_date_of_birth,
                    $age,
                    $trainee->centre_name,
                    $trainee->trainee_condition,
                ];
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        Log::info('Trainees data exported', [
            'user_id' => session('id'),
            'count' => $trainees->count()
        ]);
        
        return response()->stream($callback, 200, $headers);
    }
}