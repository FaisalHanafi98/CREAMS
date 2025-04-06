<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trainees;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class TraineeController extends Controller
{
    /**
     * Display a listing of the trainees.
     *
     * @return \Illuminate\View\View
     */
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
        
        return view('trainees.index', [
            'trainees' => $trainees
        ]);
    }

    /**
     * Show the form for creating a new trainee.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('trainees.create');
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
        
        // Handle avatar upload
        if ($request->hasFile('trainee_avatar')) {
            $avatar = $request->file('trainee_avatar');
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            $avatar->move(public_path('avatars'), $avatarName);
            $trainee->trainee_avatar = 'avatars/' . $avatarName;
        }
        
        $trainee->save();
        
        Log::info('Trainee created', [
            'user_id' => session('id'),
            'trainee_id' => $trainee->id
        ]);
        
        return redirect()->route('trainees.index')
            ->with('success', 'Trainee created successfully');
    }

    /**
     * Display the specified trainee.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $trainee = Trainees::findOrFail($id);
        
        return view('trainees.show', [
            'trainee' => $trainee
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
        
        return view('trainees.edit', [
            'trainee' => $trainee
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
            // Remove old avatar if exists
            if ($trainee->trainee_avatar && file_exists(public_path($trainee->trainee_avatar))) {
                unlink(public_path($trainee->trainee_avatar));
            }
            
            $avatar = $request->file('trainee_avatar');
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            $avatar->move(public_path('avatars'), $avatarName);
            $trainee->trainee_avatar = 'avatars/' . $avatarName;
        }
        
        $trainee->save();
        
        Log::info('Trainee updated', [
            'user_id' => session('id'),
            'trainee_id' => $trainee->id
        ]);
        
        return redirect()->route('trainees.index')
            ->with('success', 'Trainee updated successfully');
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
        
        // Delete avatar file if exists
        if ($trainee->trainee_avatar && file_exists(public_path($trainee->trainee_avatar))) {
            unlink(public_path($trainee->trainee_avatar));
        }
        
        $trainee->delete();
        
        Log::info('Trainee deleted', [
            'user_id' => session('id'),
            'trainee_id' => $id
        ]);
        
        return redirect()->route('trainees.index')
            ->with('success', 'Trainee deleted successfully');
    }
    
    /**
     * Update progress for a specific trainee.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProgress(Request $request, $id)
    {
        $trainee = Trainees::findOrFail($id);
        
        // Validate input
        $request->validate([
            'progress_notes' => 'required|string',
            'progress_date' => 'required|date',
            'progress_rating' => 'required|integer|min:1|max:5'
        ]);
        
        // In a real implementation, you would save this to a progress table
        // For now, we'll just log it
        Log::info('Trainee progress updated', [
            'user_id' => session('id'),
            'trainee_id' => $id,
            'progress_notes' => $request->progress_notes,
            'progress_date' => $request->progress_date,
            'progress_rating' => $request->progress_rating
        ]);
        
        return redirect()->back()
            ->with('success', 'Progress updated successfully');
    }
    
    /**
     * Register trainee for participation in an activity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function registerParticipation(Request $request, $id)
    {
        // In a real implementation, you would save this to a participation table
        // For now, we'll just log it
        Log::info('Trainee registered for activity', [
            'user_id' => session('id'),
            'trainee_id' => $id,
            'activity_id' => $request->activity_id
        ]);
        
        return redirect()->back()
            ->with('success', 'Trainee registered for activity successfully');
    }
    
    /**
     * Unregister trainee from an activity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unregisterParticipation(Request $request, $id)
    {
        // In a real implementation, you would remove this from a participation table
        // For now, we'll just log it
        Log::info('Trainee unregistered from activity', [
            'user_id' => session('id'),
            'trainee_id' => $id,
            'activity_id' => $request->activity_id
        ]);
        
        return redirect()->back()
            ->with('success', 'Trainee unregistered from activity successfully');
    }
}