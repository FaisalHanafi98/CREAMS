<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trainees;
use App\Models\Centres;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;

class TraineeRegistrationController extends Controller
{
    /**
     * Display the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // Get centres for dropdown - only retrieve centre_name
            $centres = Centres::where('centre_status', 'active')->get();
            
            // Get conditions for dropdown (could be from a separate model/table in the future)
            $conditions = [
                'Autism Spectrum Disorder',
                'Down Syndrome',
                'Cerebral Palsy',
                'Hearing Impairment',
                'Visual Impairment',
                'Intellectual Disability',
                'Physical Disability',
                'Speech and Language Disorder',
                'Learning Disability',
                'Multiple Disabilities',
                'Others'
            ];
            
            // Check if we have a selected centre from query parameters (for when redirected from centre page)
            $selectedCentre = null;
            if (request()->has('centre')) {
                $centreName = request()->get('centre');
                $selectedCentre = Centres::where('centre_name', $centreName)->first();
                if (!$selectedCentre) {
                    Log::warning('Invalid centre specified in trainee registration', [
                        'centre' => $centreName,
                        'user_id' => session('id')
                    ]);
                }
            }
            
            // Using the standalone view
            return view('trainees.registration', [
                'centres' => $centres,
                'conditions' => $conditions,
                'selectedCentre' => $selectedCentre
            ]);
        } catch (\Exception $e) {
            Log::error('Error accessing trainee registration page:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('traineeshome')
                ->with('error', 'Unable to access the registration page. Please try again.');
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
                'trainee_phone_number' => 'required|string|max:20',
                'trainee_date_of_birth' => 'required|date|before_or_equal:today',
                'trainee_avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'centre_name' => 'required|string|max:255|exists:centres,centre_name',
                'trainee_condition' => 'required|string|max:255',
            ], [
                'trainee_first_name.required' => 'The first name field is required.',
                'trainee_last_name.required' => 'The last name field is required.',
                'trainee_email.required' => 'The email field is required.',
                'trainee_email.email' => 'Please enter a valid email address.',
                'trainee_email.unique' => 'This email is already registered in our system.',
                'trainee_phone_number.required' => 'The phone number field is required.',
                'trainee_date_of_birth.required' => 'The date of birth field is required.',
                'trainee_date_of_birth.before_or_equal' => 'The date of birth cannot be in the future.',
                'trainee_avatar.image' => 'The uploaded file must be an image.',
                'trainee_avatar.mimes' => 'The image must be a JPEG, PNG, JPG or GIF file.',
                'trainee_avatar.max' => 'The image size must not exceed 2MB.',
                'centre_name.required' => 'Please select a centre.',
                'centre_name.exists' => 'The selected centre does not exist.',
                'trainee_condition.required' => 'Please specify the trainee\'s condition.',
            ]);
            
            if ($basicValidator->fails()) {
                Log::warning('Trainee basic validation failed', [
                    'errors' => $basicValidator->errors()->toArray(),
                    'user_id' => session('id')
                ]);
                
                return redirect()->back()
                    ->withErrors($basicValidator)
                    ->withInput()
                    ->with('error_tab', 'basic'); // Indicates which tab has errors
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
                Log::warning('Guardian validation failed', [
                    'errors' => $guardianValidator->errors()->toArray(),
                    'user_id' => session('id')
                ]);
                
                return redirect()->back()
                    ->withErrors($guardianValidator)
                    ->withInput()
                    ->with('error_tab', 'guardian'); // Indicates which tab has errors
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
                Log::warning('Additional information validation failed', [
                    'errors' => $additionalValidator->errors()->toArray(),
                    'user_id' => session('id')
                ]);
                
                return redirect()->back()
                    ->withErrors($additionalValidator)
                    ->withInput()
                    ->with('error_tab', 'additional'); // Indicates which tab has errors
            }
            
            // Create new trainee record
            $trainee = new Trainees();
            $trainee->trainee_first_name = $request->input('trainee_first_name');
            $trainee->trainee_last_name = $request->input('trainee_last_name');
            $trainee->trainee_email = $request->input('trainee_email');
            $trainee->trainee_phone_number = $request->input('trainee_phone_number');
            $trainee->trainee_date_of_birth = $request->input('trainee_date_of_birth');
            $trainee->centre_name = $request->input('centre_name'); // Just store the centre_name
            $trainee->trainee_condition = $request->input('trainee_condition');
            $trainee->trainee_attendance = 0; // Default attendance value
            
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
            
            // Handle avatar upload with improved naming
            if ($request->hasFile('trainee_avatar')) {
                try {
                    $avatar = $request->file('trainee_avatar');
                    
                    // Generate unique filename related to the trainee
                    $filename = $this->generateAvatarFilename($trainee, $avatar);
                    
                    // Process and optimize the image
                    $this->processAndStoreAvatar($avatar, $filename, $trainee);
                } catch (\Exception $e) {
                    Log::error('Error processing trainee avatar:', [
                        'trainee_id' => $trainee->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    // Set default avatar but continue with registration
                    $trainee->trainee_avatar = 'images/default-avatar.jpg';
                    $trainee->save();
                }
            } else {
                // Set default avatar
                $trainee->trainee_avatar = 'images/default-avatar.jpg';
                $trainee->save();
            }
            
            // Commit the transaction
            DB::commit();
            
            // Log the successful registration
            Log::info('Trainee registered successfully', [
                'trainee_id' => $trainee->id,
                'trainee_name' => $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name,
                'registered_by' => session('id'),
                'centre' => $trainee->centre_name
            ]);
            
            return redirect()->route('traineeshome')
                ->with('success', 'Trainee registered successfully.');
                
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database query exceptions
            DB::rollBack();
            
            Log::error('Database error while registering trainee:', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'Unknown SQL',
                'bindings' => $e->getBindings() ?? [],
                'code' => $e->getCode(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()
                ->with('error', 'A database error occurred. Please contact system administrator.')
                ->withInput();
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation exceptions (should be caught by validators above, but just in case)
            DB::rollBack();
            
            Log::error('Validation error while registering trainee:', [
                'error' => $e->getMessage(),
                'errors' => $e->errors(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            // Handle any other exceptions
            DB::rollBack();
            
            Log::error('Error registering trainee:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id'),
                'request' => $request->except(['trainee_avatar']),
                'request_has_file' => $request->hasFile('trainee_avatar')
            ]);
            
            $errorMessage = 'An error occurred while registering the trainee. ';
            
            // Provide more specific error messages based on the exception
            if ($e instanceof \Illuminate\Filesystem\FileNotFoundException) {
                $errorMessage .= 'Error uploading avatar file. Please try again with a different image.';
            } elseif ($e instanceof \ErrorException && strpos($e->getMessage(), 'mkdir') !== false) {
                $errorMessage .= 'Error creating directories. Please contact system administrator.';
            } else {
                $errorMessage .= 'Please try again or contact support if the issue persists.';
            }
            
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }
    
    /**
     * Generate a unique filename for the trainee avatar
     *
     * @param Trainees $trainee
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    private function generateAvatarFilename(Trainees $trainee, $file)
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
     * @param Trainees $trainee
     * @return void
     */
    private function processAndStoreAvatar($file, $filename, Trainees &$trainee)
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
        $trainee->trainee_avatar = $filePath;
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
            $exists = Trainees::where('trainee_email', $email)->exists();
            
            return response()->json([
                'valid' => !$exists,
                'message' => $exists ? 'Email already exists' : 'Email is available'
            ]);
        } catch (\Exception $e) {
            Log::error('Error validating trainee email:', [
                'error' => $e->getMessage(),
                'email' => $request->input('email')
            ]);
            
            return response()->json([
                'valid' => false,
                'message' => 'Error validating email'
            ], 500);
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
            
            Log::info('Trainees import initiated', [
                'user_id' => session('id'),
                'filename' => $request->file('trainees_file')->getClientOriginalName()
            ]);
            
            // For now return with a placeholder message
            return redirect()->route('traineeshome')
                ->with('info', 'Import functionality is under development.');
                
        } catch (\Exception $e) {
            Log::error('Error importing trainees:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred during import: ' . $e->getMessage());
        }
    }
}