<?php

namespace App\Http\Controllers\Trainee;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Trainee;
use App\Models\Centre;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesErrors;

class TraineeController extends Controller
{
    use HandlesErrors;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        // All authenticated staff can view trainees, but with centre restrictions
        $this->middleware('enhanced.role:admin,supervisor,teacher,ajk');
        
        // Only admin and supervisors can create/edit/delete trainees
        $this->middleware('enhanced.role:admin,supervisor')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of all trainees.
     */
    public function index()
    {
        try {
            $role = session('role');
            $userId = session('id');
            
            Log::info('Trainee index accessed', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            // Get trainees with relationships
            $query = Trainee::with(['centre']);
            
            // Filter by user role and centre if needed
            if (in_array($role, ['teacher', 'supervisor', 'ajk'])) {
                $userCentreId = session('centre_id');
                if ($userCentreId) {
                    $query->where('centre_id', $userCentreId);
                }
            }
            
            $trainees = $query->orderBy('created_at', 'desc')->paginate(12);
            
            // Get statistics
            $stats = [
                'total_trainees' => $trainees->total(),
                'active_trainees' => Trainee::where('status', 'active')->count(),
                'new_this_month' => Trainee::where('created_at', '>=', now()->subMonth())->count(),
                'centres_count' => Centre::count()
            ];
            
            return view('trainees.home', compact('trainees', 'stats', 'role'));
            
        } catch (\Exception $e) {
            Log::error('Error loading trainees index', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'Unable to load trainees. Please try again.');
        }
    }

    /**
     * Show the form for creating a new trainee.
     */
    public function create()
    {
        try {
            $role = session('role');
            
            // Check permissions
            if (!in_array($role, ['admin', 'supervisor', 'teacher'])) {
                return redirect()->route('trainees.index')
                    ->with('error', 'You do not have permission to create trainees.');
            }
            
            Log::info('Trainee create form accessed', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            // Get active centres
            $centres = Centre::where('is_active', 1)
                ->orderBy('centre_name')
                ->get();
            
            return view('trainees.create', compact('centres'));
            
        } catch (\Exception $e) {
            Log::error('Error loading trainee create form', [
                'message' => $e->getMessage(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('trainees.index')
                ->with('error', 'Unable to load registration form.');
        }
    }

    /**
     * Store a newly created trainee in storage.
     */
    public function store(Request $request)
    {
        try {
            $role = session('role');
            $userId = session('id');
            
            Log::info('Trainee registration initiated', [
                'user_id' => $userId,
                'role' => $role,
                'data' => $request->except(['avatar', 'password'])
            ]);
            
            // Check permissions
            if (!in_array($role, ['admin', 'supervisor', 'teacher'])) {
                return redirect()->route('trainees.index')
                    ->with('error', 'You do not have permission to create trainees.');
            }
            
            // Validate input - updated to match form field names and database architecture
            $validated = $request->validate([
                'trainee_first_name' => 'required|string|max:255',
                'trainee_last_name' => 'required|string|max:255',
                'trainee_email' => 'required|email|unique:trainees,trainee_email',
                'ic_number' => 'required|string|max:20|unique:trainees,ic_number',
                'trainee_phone_number' => 'required|string|max:20',
                'trainee_date_of_birth' => 'required|date|before:today',
                'gender' => 'required|string|in:Male,Female',
                'trainee_avatar' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif',
                'centre_name' => 'required|string|exists:centres,centre_name',
                'trainee_condition' => 'required|string|in:Physical Disabilities,Learning Support,Visual Impairment,Autism Spectrum Support,Hearing Impairment,Speech Therapy',
                'trainee_address' => 'nullable|string|max:500',
                // Mandatory consent fields (required by database architecture)
                'photo_consent' => 'required|accepted',
                'services_consent' => 'required|accepted',
                // Guardian fields
                'guardian_name' => 'nullable|string|max:255',
                'guardian_relationship' => 'required|string|max:255',
                'guardian_phone' => 'nullable|string|max:20',
                'guardian_email' => 'required|email|max:255',
                'guardian_address' => 'nullable|string|max:500',
                // Emergency contact fields
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'emergency_contact_relationship' => 'nullable|string|max:255',
                // Medical and additional info
                'medical_history' => 'nullable|string|max:1000',
                'additional_notes' => 'nullable|string|max:1000',
                'consent' => 'required|accepted',
            ], [
                'trainee_first_name.required' => 'First name is required.',
                'trainee_last_name.required' => 'Last name is required.',
                'trainee_email.required' => 'Email address is required.',
                'trainee_email.unique' => 'This email is already registered.',
                'ic_number.required' => 'IC/Passport number is required.',
                'ic_number.unique' => 'This IC/Passport number is already registered.',
                'trainee_date_of_birth.before' => 'Date of birth must be in the past.',
                'trainee_avatar.image' => 'Avatar must be a valid image file.',
                'trainee_avatar.max' => 'Avatar file size must not exceed 2MB.',
                'centre_name.exists' => 'Selected centre is invalid.',
                'guardian_relationship.required' => 'Guardian relationship is required.',
                'guardian_email.required' => 'Guardian email is required.',
                'trainee_condition.in' => 'Please select a valid service category from the list.',
                'photo_consent.required' => 'Photo/video consent is mandatory for service provision.',
                'photo_consent.accepted' => 'You must provide photo/video consent to proceed.',
                'services_consent.required' => 'Service provision consent is mandatory for registration.',
                'services_consent.accepted' => 'You must provide service consent to proceed.',
                'consent.required' => 'You must provide consent to register this trainee.',
                'consent.accepted' => 'You must provide consent to register this trainee.',
            ]);
            
            DB::beginTransaction();
            
            // Handle avatar upload
            $avatarPath = null;
            if ($request->hasFile('trainee_avatar')) {
                $avatarPath = $request->file('trainee_avatar')->store('trainee-avatars', 'public');
                Log::info('Trainee avatar uploaded', [
                    'path' => $avatarPath,
                    'user_id' => $userId
                ]);
            }
            
            // Get centre_id from centre_name
            $centre = Centre::where('centre_name', $validated['centre_name'])->first();
            if (!$centre) {
                throw new \Exception('Centre not found');
            }
            
            // Generate disability-specific trainee_id (aligned with database architecture)
            $disabilityPrefixMap = [
                'Physical Disabilities' => 'PHY',
                'Learning Support' => 'LEA', 
                'Visual Impairment' => 'VIS',
                'Autism Spectrum Support' => 'AUT',
                'Hearing Impairment' => 'HEA',
                'Speech Therapy' => 'SPE'
            ];
            
            $prefix = $disabilityPrefixMap[$validated['trainee_condition']] ?? 'GEN';
            
            // Find the next available number for this disability category
            $lastTrainee = Trainee::where('trainee_id', 'like', "{$prefix}%")
                                  ->orderByRaw('CAST(SUBSTRING(trainee_id, 4) AS UNSIGNED) DESC')
                                  ->first();
            
            if ($lastTrainee) {
                $lastNumber = (int) substr($lastTrainee->trainee_id, 3);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1001; // Start from 1001 for each category
            }
            
            $traineeId = $prefix . $newNumber;
            $uniqueIdentifier = $traineeId; // Use same value for consistency
            
            // Create trainee with all required fields
            $trainee = Trainee::create([
                'trainee_id' => $traineeId,
                'unique_identifier' => $uniqueIdentifier,
                'trainee_first_name' => $validated['trainee_first_name'],
                'trainee_last_name' => $validated['trainee_last_name'],
                'trainee_email' => $validated['trainee_email'],
                'ic_number' => $validated['ic_number'],
                'trainee_phone_number' => $validated['trainee_phone_number'],
                'trainee_date_of_birth' => $validated['trainee_date_of_birth'],
                'gender' => $validated['gender'],
                'avatar' => $avatarPath,
                'centre_name' => $validated['centre_name'],
                'centre_id' => $centre->centre_id,
                'trainee_condition' => $validated['trainee_condition'],
                'trainee_address' => $validated['trainee_address'],
                // Mandatory consent fields (required by database architecture)
                'photo_consent' => $validated['photo_consent'] ? 1 : 0,
                'services_consent' => $validated['services_consent'] ? 1 : 0,
                // Guardian information
                'guardian_name' => $validated['guardian_name'],
                'guardian_relationship' => $validated['guardian_relationship'],
                'guardian_phone' => $validated['guardian_phone'],
                'guardian_email' => $validated['guardian_email'],
                'guardian_address' => $validated['guardian_address'],
                // Emergency contact information
                'emergency_contact_name' => $validated['emergency_contact_name'],
                'emergency_contact_phone' => $validated['emergency_contact_phone'],
                'emergency_contact_relationship' => $validated['emergency_contact_relationship'],
                // Medical and additional information
                'medical_history' => $validated['medical_history'],
                'additional_notes' => $validated['additional_notes'],
                'status' => 'active',
            ]);
            
            DB::commit();

            // Log activity
            \App\Models\ActivityLog::log([
                'action_type' => 'created',
                'model_type' => 'Trainee',
                'model_id' => $trainee->id,
                'title' => 'New Trainee Registered: ' . $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name,
                'description' => 'Trainee ID: ' . $trainee->trainee_id . ' | Condition: ' . $trainee->trainee_condition,
                'icon' => 'user-plus',
                'status' => 'success'
            ]);

            Log::info('Trainee registered successfully', [
                'trainee_id' => $trainee->id,
                'name' => $trainee->full_name,
                'registered_by' => $userId
            ]);

            return redirect()->route('trainees.show', $trainee->id)
                ->with('success', 'Trainee registered successfully!');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('Trainee registration validation failed', [
                'errors' => $e->errors(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Trainee registration error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id'),
                'input' => $request->except(['avatar'])
            ]);
            
            return redirect()->back()
                ->with('error', 'Registration failed. Please check the logs for more information.')
                ->withInput();
        }
    }

    /**
     * Display the specified trainee.
     */
    public function show($id)
    {
        try {
            $trainee = Trainee::with(['centre', 'activities', 'attendances'])
                ->findOrFail($id);
            
            Log::info('Trainee profile viewed', [
                'trainee_id' => $trainee->id,
                'viewer_id' => session('id'),
                'viewer_role' => session('role')
            ]);
            
            // Get trainee statistics
            $stats = [
                'total_activities' => $trainee->activities->count(),
                'attendance_rate' => $this->calculateAttendanceRate($trainee),
                'days_enrolled' => $trainee->created_at->diffInDays(now()),
                'last_activity' => $trainee->attendances->last()?->created_at
            ];
            
            return view('trainees.show', compact('trainee', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Error showing trainee profile', [
                'trainee_id' => $id,
                'message' => $e->getMessage(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('trainees.index')
                ->with('error', 'Trainee not found.');
        }
    }

    /**
     * Show the form for editing the specified trainee.
     */
    public function edit($id)
    {
        try {
            $role = session('role');
            
            // Check permissions
            if (!in_array($role, ['admin', 'supervisor', 'teacher'])) {
                return redirect()->route('trainees.show', $id)
                    ->with('error', 'You do not have permission to edit trainees.');
            }
            
            $trainee = Trainee::findOrFail($id);
            $centres = Centre::where('is_active', 1)
                ->orderBy('centre_name')
                ->get();
            
            Log::info('Trainee edit form accessed', [
                'trainee_id' => $trainee->id,
                'editor_id' => session('id'),
                'editor_role' => $role
            ]);
            
            return view('trainees.edit', compact('trainee', 'centres'));
            
        } catch (\Exception $e) {
            Log::error('Error loading trainee edit form', [
                'trainee_id' => $id,
                'message' => $e->getMessage(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('trainees.index')
                ->with('error', 'Trainee not found.');
        }
    }

    /**
     * Update the specified trainee in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $role = session('role');
            $userId = session('id');
            
            // Check permissions
            if (!in_array($role, ['admin', 'supervisor', 'teacher'])) {
                return redirect()->route('trainees.show', $id)
                    ->with('error', 'You do not have permission to update trainees.');
            }
            
            $trainee = Trainee::findOrFail($id);
            
            Log::info('Trainee update initiated', [
                'trainee_id' => $trainee->id,
                'editor_id' => $userId,
                'editor_role' => $role
            ]);
            
            // Validate input - updated to match form field names and database architecture
            $validated = $request->validate([
                'trainee_first_name' => 'required|string|max:255',
                'trainee_last_name' => 'required|string|max:255',
                'trainee_email' => 'required|email|unique:trainees,trainee_email,' . $id,
                'trainee_phone_number' => 'required|string|max:20',
                'trainee_date_of_birth' => 'required|date|before:today',
                'gender' => 'required|string|in:Male,Female',
                'trainee_avatar' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif',
                'centre_name' => 'required|string|exists:centres,centre_name',
                'trainee_condition' => 'required|string|in:Physical Disabilities,Learning Support,Visual Impairment,Autism Spectrum Support,Hearing Impairment,Speech Therapy',
                'trainee_address' => 'nullable|string|max:500',
                // Consent fields (optional for updates as they may already exist)
                'photo_consent' => 'nullable|boolean',
                'services_consent' => 'nullable|boolean',
                // Guardian fields
                'guardian_name' => 'nullable|string|max:255',
                'guardian_relationship' => 'required|string|max:255',
                'guardian_phone' => 'nullable|string|max:20',
                'guardian_email' => 'required|email|max:255',
                'guardian_address' => 'nullable|string|max:500',
                // Emergency contact fields
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'emergency_contact_relationship' => 'nullable|string|max:255',
                // Medical and additional info
                'medical_history' => 'nullable|string|max:1000',
                'additional_notes' => 'nullable|string|max:1000',
                'status' => 'nullable|string|in:active,inactive,completed',
            ]);
            
            DB::beginTransaction();
            
            // Handle avatar upload
            if ($request->hasFile('trainee_avatar')) {
                // Delete old avatar if exists
                if ($trainee->avatar) {
                    Storage::disk('public')->delete($trainee->avatar);
                }
                
                $validated['avatar'] = $request->file('trainee_avatar')->store('trainee-avatars', 'public');
                Log::info('Trainee avatar updated', [
                    'trainee_id' => $trainee->id,
                    'new_path' => $validated['avatar']
                ]);
            }
            
            // Update trainee
            $trainee->update($validated);

            DB::commit();

            // Log activity
            \App\Models\ActivityLog::log([
                'action_type' => 'updated',
                'model_type' => 'Trainee',
                'model_id' => $trainee->id,
                'title' => 'Trainee Profile Updated: ' . $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name,
                'description' => 'Trainee ID: ' . $trainee->trainee_id,
                'icon' => 'user-edit',
                'status' => 'info'
            ]);

            Log::info('Trainee updated successfully', [
                'trainee_id' => $trainee->id,
                'updated_by' => $userId
            ]);

            return redirect()->route('trainees.show', $trainee->id)
                ->with('success', 'Trainee updated successfully!');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('Trainee update validation failed', [
                'trainee_id' => $id,
                'errors' => $e->errors(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Trainee update error', [
                'trainee_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()
                ->with('error', 'Update failed. Please check the logs for more information.')
                ->withInput();
        }
    }

    /**
     * Remove the specified trainee from storage.
     */
    public function destroy($id)
    {
        try {
            $role = session('role');
            $userId = session('id');
            
            // Check permissions - only admin can delete
            if ($role !== 'admin') {
                return redirect()->route('trainees.show', $id)
                    ->with('error', 'Only administrators can delete trainees.');
            }
            
            $trainee = Trainee::findOrFail($id);
            
            Log::warning('Trainee deletion initiated', [
                'trainee_id' => $trainee->id,
                'trainee_name' => $trainee->full_name,
                'deleted_by' => $userId
            ]);
            
            DB::beginTransaction();
            
            // Delete avatar if exists
            if ($trainee->avatar) {
                Storage::disk('public')->delete($trainee->avatar);
            }
            
            // Store name for message
            $traineeName = $trainee->full_name;
            $traineeIdNumber = $trainee->trainee_id;

            // Log activity BEFORE deletion
            \App\Models\ActivityLog::log([
                'action_type' => 'deleted',
                'model_type' => 'Trainee',
                'model_id' => $trainee->id,
                'title' => 'Trainee Deleted: ' . $traineeName,
                'description' => 'Trainee ID: ' . $traineeIdNumber,
                'icon' => 'user-times',
                'status' => 'warning'
            ]);

            // Delete trainee
            $trainee->delete();

            DB::commit();

            Log::info('Trainee deleted successfully', [
                'trainee_id' => $id,
                'trainee_name' => $traineeName,
                'deleted_by' => $userId
            ]);
            
            return redirect()->route('trainees.index')
                ->with('success', "Trainee {$traineeName} has been deleted successfully.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Trainee deletion error', [
                'trainee_id' => $id,
                'message' => $e->getMessage(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()
                ->with('error', 'Deletion failed. Please try again.');
        }
    }

    /**
     * Calculate attendance rate for a trainee
     */
    private function calculateAttendanceRate($trainee)
    {
        $totalSessions = $trainee->activities->sum('total_sessions') ?? 0;
        $attendedSessions = $trainee->attendances->where('status', 'present')->count();
        
        if ($totalSessions == 0) {
            return 0;
        }
        
        return round(($attendedSessions / $totalSessions) * 100, 2);
    }
}