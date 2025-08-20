<?php

namespace App\Http\Controllers\Trainee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trainee;
use App\Models\Centre;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use App\Traits\HandlesErrors;
use App\Exceptions\FileUploadException;
use App\Exceptions\ValidationException as CREAMSValidationException;

class TraineeRegistrationController extends Controller
{
    use HandlesErrors;
    /**
     * Display the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // Get centres for dropdown - only retrieve centre_name
            // Get centres with safe query
            try {
                $centres = Centre::where('is_active', 1)->get();
            } catch (\Exception $e) {
                // If is_active column doesn't exist, get all centres
                $centres = Centre::all();
            }
            
            // Get conditions aligned with Welcome Page services
            $conditions = [
                'Physical Disabilities',
                'Learning Support', 
                'Visual Impairment',
                'Autism Spectrum Support',
                'Hearing Impairment',
                'Speech Therapy'
            ];
            
            // Check if we have a selected centre from query parameters (for when redirected from centre page)
            $selectedCentre = null;
            if (request()->has('centre')) {
                $centreName = request()->get('centre');
                $selectedCentre = Centre::where('centre_name', $centreName)->first();
                if (!$selectedCentre) {
                    Log::warning('Invalid centre specified in trainee registration', [
                        'centre' => $centreName,
                        'user_id' => session('id')
                    ]);
                }
            }
            
            // Using the new standardized layout view
            return view('trainees.create', [
                'centres' => $centres,  // Note: using 'centres' to match the view variable name
                'conditions' => $conditions,
                'selectedCentre' => $selectedCentre,
                'trainee' => null,  // For create form compatibility
                'isEdit' => false,
                'action' => route('trainees.store')
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'loading trainee registration page', [
                'redirect_route' => 'trainees.home'
            ]);
        }
    }

    /**
     * Store a newly created trainee.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Start a database transaction
        DB::beginTransaction();
        
        try {
            // Step 1: Validate basic trainee information
            $basicValidator = Validator::make($request->all(), [
                'trainee_first_name' => 'required|string|max:255',
                'trainee_last_name' => 'required|string|max:255',
                'trainee_email' => 'required|email|unique:trainees,trainee_email',
                'ic_number' => 'required|string|max:20|unique:trainees,ic_number',
                'trainee_phone_number' => 'required|string|max:20',
                'trainee_date_of_birth' => 'required|date|before_or_equal:today',
                'gender' => 'required|in:Male,Female',
                'trainee_avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'centre_name' => 'required|string|max:255|exists:centres,centre_name',
                'trainee_condition' => 'required|string|max:255',
            ], [
                'trainee_first_name.required' => 'The first name field is required.',
                'trainee_last_name.required' => 'The last name field is required.',
                'trainee_email.required' => 'The email field is required.',
                'trainee_email.email' => 'Please enter a valid email address.',
                'trainee_email.unique' => 'This email is already registered in our system.',
                'ic_number.required' => 'The IC number field is required.',
                'ic_number.unique' => 'This IC number is already registered in our system.',
                'trainee_phone_number.required' => 'The phone number field is required.',
                'trainee_date_of_birth.required' => 'The date of birth field is required.',
                'trainee_date_of_birth.before_or_equal' => 'The date of birth cannot be in the future.',
                'gender.required' => 'Please select a gender.',
                'gender.in' => 'Gender must be either Male or Female.',
                'trainee_avatar.image' => 'The uploaded file must be an image.',
                'trainee_avatar.mimes' => 'The image must be a JPEG, PNG, JPG or GIF file.',
                'trainee_avatar.max' => 'The image size must not exceed 2MB.',
                'centre_name.required' => 'Please select a centre.',
                'centre_name.exists' => 'The selected centre does not exist.',
                'trainee_condition.required' => 'Please specify the trainee\'s condition.',
            ]);
            
            if ($basicValidator->fails()) {
                $this->logUserAction('trainee registration validation failed - basic info', [
                    'errors' => $basicValidator->errors()->toArray()
                ]);
                
                return redirect()->back()
                    ->withErrors($basicValidator)
                    ->withInput()
                    ->with('error_tab', 'basic');
            }
            
            // Step 2: Validate guardian information
            $guardianValidator = Validator::make($request->all(), [
                'guardian_name' => 'required|string|max:255',
                'guardian_relationship' => 'required|string|max:255',
                'guardian_phone' => 'required|string|max:20',
                'guardian_email' => 'required|email|max:255',
                'guardian_address' => 'nullable|string|max:500',
            ], [
                'guardian_name.required' => 'The guardian name field is required.',
                'guardian_relationship.required' => 'Please specify the relationship to the trainee.',
                'guardian_phone.required' => 'The guardian phone number is required.',
                'guardian_email.required' => 'The guardian email address is required.',
                'guardian_email.email' => 'Please enter a valid email address for the guardian.',
            ]);
            
            if ($guardianValidator->fails()) {
                $this->logUserAction('trainee registration validation failed - guardian info', [
                    'errors' => $guardianValidator->errors()->toArray()
                ]);
                
                return redirect()->back()
                    ->withErrors($guardianValidator)
                    ->withInput()
                    ->with('error_tab', 'guardian');
            }
            
            // Step 3: Validate additional information (optional fields)
            $additionalValidator = Validator::make($request->all(), [
                'additional_notes' => 'nullable|string|max:5000',
                'medical_history' => 'nullable|string|max:5000',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'emergency_contact_relationship' => 'nullable|string|max:255',
                'consent' => 'required|accepted',
            ], [
                'consent.required' => 'You must provide consent to register this trainee.',
                'consent.accepted' => 'You must provide consent to register this trainee.',
            ]);
            
            if ($additionalValidator->fails()) {
                $this->logUserAction('trainee registration validation failed - additional info', [
                    'errors' => $additionalValidator->errors()->toArray()
                ]);
                
                return redirect()->back()
                    ->withErrors($additionalValidator)
                    ->withInput()
                    ->with('error_tab', 'additional');
            }
            
            // Create new trainee record
            $trainee = new Trainee();
            $trainee->trainee_first_name = $request->input('trainee_first_name');
            $trainee->trainee_last_name = $request->input('trainee_last_name');
            $trainee->trainee_email = $request->input('trainee_email');
            $trainee->ic_number = $request->input('ic_number');
            $trainee->trainee_phone_number = $request->input('trainee_phone_number');
            $trainee->trainee_date_of_birth = $request->input('trainee_date_of_birth');
            $trainee->gender = $request->input('gender');
            $trainee->centre_name = $request->input('centre_name'); // Just store the centre_name
            
            // Get centre_id from centre_name for proper ID generation
            $centre = Centre::where('centre_name', $request->input('centre_name'))->first();
            if ($centre) {
                $trainee->centre_id = $centre->centre_id;
            } else {
                $trainee->centre_id = '001'; // Default fallback
            }
            
            // Force generate trainee_id before saving to ensure it's set
            if (!$trainee->trainee_id) {
                $year = date('Y');
                $centreId = $trainee->centre_id ?? '001';
                
                // Get the next sequence number for this year and centre
                $lastTrainee = Trainee::where('trainee_id', 'LIKE', "TRN{$year}{$centreId}%")
                    ->orderBy('trainee_id', 'desc')
                    ->first();
                    
                if ($lastTrainee) {
                    $lastSequence = intval(substr($lastTrainee->trainee_id, -4));
                    $nextSequence = $lastSequence + 1;
                } else {
                    $nextSequence = 1;
                }
                
                $trainee->trainee_id = 'TRN' . $year . $centreId . sprintf('%04d', $nextSequence);
            }
            
            // Note: unique_identifier field removed as it doesn't exist in database schema
            
            $trainee->trainee_condition = $request->input('trainee_condition');
            // Note: trainee_attendance field removed as it doesn't exist in database
            
            // Add guardian information
            $trainee->guardian_name = $request->input('guardian_name');
            $trainee->guardian_relationship = $request->input('guardian_relationship');
            $trainee->guardian_phone = $request->input('guardian_phone');
            $trainee->guardian_email = $request->input('guardian_email');
            $trainee->guardian_address = $request->input('guardian_address');
            
            // Add additional information
            $trainee->medical_history = $request->input('medical_history');
            $trainee->additional_notes = $request->input('additional_notes');
            $trainee->emergency_contact_name = $request->input('emergency_contact_name');
            $trainee->emergency_contact_phone = $request->input('emergency_contact_phone');
            $trainee->emergency_contact_relationship = $request->input('emergency_contact_relationship');
            
            // Save the trainee to get an ID (needed for avatar naming)
            $trainee->save();
            
            // Handle avatar upload with enhanced error handling
            if ($request->hasFile('trainee_avatar')) {
                try {
                    $avatar = $request->file('trainee_avatar');
                    
                    // Validate the uploaded file
                    $this->validateFileUpload($avatar, [
                        'max_size' => 2048,
                        'allowed_types' => ['jpeg', 'jpg', 'png', 'gif']
                    ]);
                    
                    // Generate unique filename related to the trainee
                    $filename = $this->generateAvatarFilename($trainee, $avatar);
                    
                    // Process and optimize the image
                    $this->processAndStoreAvatar($avatar, $filename, $trainee);
                    
                    $this->logUserAction('trainee avatar uploaded successfully', [
                        'trainee_id' => $trainee->id,
                        'filename' => $filename
                    ]);
                } catch (FileUploadException $e) {
                    $this->logUserAction('trainee avatar upload failed', [
                        'trainee_id' => $trainee->id,
                        'error' => $e->getMessage()
                    ]);
                    
                    // Set default avatar but continue with registration
                    $trainee->avatar = 'images/default-avatar.png';
                    $trainee->save();
                } catch (\Exception $e) {
                    $this->logUserAction('trainee avatar upload error', [
                        'trainee_id' => $trainee->id,
                        'error' => $e->getMessage()
                    ]);
                    
                    // Set default avatar but continue with registration
                    $trainee->avatar = 'images/default-avatar.png';
                    $trainee->save();
                }
            } else {
                // Set default avatar
                $trainee->avatar = 'images/default-avatar.png';
                $trainee->save();
            }
            
            // Commit the transaction
            DB::commit();
            
            // Log the successful registration
            $this->logUserAction('trainee registered successfully', [
                'trainee_id' => $trainee->id,
                'trainee_name' => $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name,
                'centre' => $trainee->centre_name
            ]);
            
            return $this->successResponse(
                'Trainee registered successfully.',
                null,
                'trainees.home'
            );
                
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'registering trainee', [
                'request_data' => $request->except(['trainee_avatar', '_token']),
                'has_avatar_file' => $request->hasFile('trainee_avatar')
            ]);
        }
    }
    
    /**
     * Generate a unique filename for the trainee avatar
     *
     * @param Trainee $trainee
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    private function generateAvatarFilename(Trainee $trainee, $file)
    {
        // Extract the file extension
        $extension = $file->getClientOriginalExtension();
        
        // Get trainee initials and sanitize them
        $firstInitial = substr($trainee->trainee_first_name, 0, 1);
        $lastInitial = substr($trainee->trainee_last_name, 0, 1);
        $initials = strtoupper($firstInitial . $lastInitial);
        
        // Generate safe initials (remove any non-alphanumeric characters)
        $safeInitials = preg_replace("/[^A-Z]/", "", $initials);
        if (empty($safeInitials)) {
            $safeInitials = "XX";
        }
        
        // Get a safe version of the last name for the filename
        $safeLastName = Str::slug(substr($trainee->trainee_last_name, 0, 10));
        if (empty($safeLastName)) {
            $safeLastName = 'unknown';
        }
        
        // Create unique filename with traineeID, initials, last name, and timestamp
        $timestamp = Carbon::now()->format('YmdHis');
        return "trainee_{$trainee->id}_{$safeInitials}_{$safeLastName}_{$timestamp}.{$extension}";
    }
    
    /**
     * Process and store the avatar image
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $filename
     * @param Trainee $trainee
     * @return void
     */
    private function processAndStoreAvatar($file, $filename, Trainee &$trainee)
    {
        // Create storage directory if it doesn't exist
        $storagePath = public_path('storage/trainee_avatars');
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        
        // Define path for the image
        $filePath = 'storage/trainee_avatars/' . $filename;
        $fullPath = public_path($filePath);
        
        // Check if we have the Intervention Image library
        if (class_exists('Intervention\Image\Facades\Image')) {
            // Process the image - resize and optimize
            $img = Image::make($file->getRealPath());
            
            // Resize to standard dimensions while maintaining aspect ratio
            $img->fit(300, 300, function ($constraint) {
                $constraint->upsize();
            });
            
            // Save the processed image with medium quality to reduce file size
            $img->save($fullPath, 80);
        } else {
            // Fallback if Intervention Image isn't available
            $file->move(public_path('storage/trainee_avatars'), $filename);
        }
        
        // Update trainee with the avatar path
        $trainee->avatar = $filePath;
        $trainee->save();
    }
    
    /**
     * Validate trainee email for AJAX requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateEmail(Request $request)
    {
        try {
            $email = $request->input('email');
            
            // Check if email exists
            $exists = Trainee::where('trainee_email', $email)->exists();
            
            return response()->json([
                'valid' => !$exists,
                'message' => $exists ? 'Email already exists' : 'Email is available'
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'validating trainee email', [
                'email' => $request->input('email')
            ]);
        }
    }
    
    /**
     * Import trainees from CSV/Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        try {
            // Validate file
            $validator = Validator::make($request->all(), [
                'trainees_file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            // Here you would implement the actual import logic
            // This would typically use a package like maatwebsite/excel
            // For now, we'll just log that the import was attempted
            
            Log::info('Trainee import initiated', [
                'user_id' => session('id'),
                'filename' => $request->file('trainees_file')->getClientOriginalName()
            ]);
            
            // For now return with a placeholder message
            return redirect()->route('trainees.home')
                ->with('info', 'Import functionality is under development.');
                
        } catch (\Exception $e) {
            return $this->handleException($e, 'importing trainees', [
                'filename' => $request->hasFile('trainees_file') ? $request->file('trainees_file')->getClientOriginalName() : 'unknown'
            ]);
        }
    }
}