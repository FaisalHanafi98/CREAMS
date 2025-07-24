<?php

namespace App\Http\Controllers;

use App\Models\Trainee;
use App\Models\TraineeAuditLog;
use App\Models\TraineeDocument;
use App\Models\TraineeProgress;
use App\Services\TraineeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EnhancedTraineeController extends Controller
{
    protected $traineeService;
    
    public function __construct(TraineeService $traineeService)
    {
        $this->traineeService = $traineeService;
    }
    
    /**
     * Enhanced index with advanced filtering and search
     */
    public function index(Request $request)
    {
        try {
            // Check authentication
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            $centreId = session('centre_id');
            
            // Build filters from request
            $filters = $request->only([
                'search', 'status', 'condition', 'age_from', 'age_to', 
                'admission_from', 'admission_to', 'tags', 'progress_status'
            ]);

            // Get filtered trainees query
            $query = $this->traineeService->searchTrainees($filters, $centreId);
            
            // Sorting
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            // Validate sort field to prevent SQL injection
            $allowedSortFields = [
                'name', 'trainee_first_name', 'trainee_last_name', 'unique_identifier',
                'status', 'trainee_condition', 'admission_date', 'created_at'
            ];
            
            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            // Paginate results
            $trainees = $query->paginate(20)->withQueryString();
            
            // Get filter options for dropdowns
            $conditions = Trainee::where('centre_id', $centreId)
                ->distinct()
                ->pluck('trainee_condition')
                ->filter()
                ->sort()
                ->values();
                
            $statuses = ['active', 'inactive', 'graduated', 'transferred'];
            
            // Get statistics
            $stats = $this->traineeService->getStatistics($centreId);
            
            // Get upcoming birthdays
            $upcomingBirthdays = $this->traineeService->getUpcomingBirthdays(30);
            
            return view('trainees.enhanced-index', compact(
                'trainees', 'conditions', 'statuses', 'stats', 'upcomingBirthdays', 'filters'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Enhanced trainee index failed', [
                'user_id' => session('id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to load trainees: ' . $e->getMessage());
        }
    }
    
    /**
     * Show create form
     */
    public function create()
    {
        if (!session()->has('id')) {
            return redirect()->route('login');
        }

        $conditions = config('trainee.conditions', [
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
        ]);
        
        return view('trainees.enhanced-create', compact('conditions'));
    }
    
    /**
     * Store new trainee with comprehensive validation
     */
    public function store(Request $request)
    {
        try {
            // Enhanced validation rules
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:trainees,trainee_email',
                'phone' => 'nullable|string|max:20',
                'date_of_birth' => 'required|date|before:today',
                'gender' => 'required|in:male,female,other',
                'address' => 'nullable|string|max:500',
                'condition' => 'required|string|max:255',
                'medical_history' => 'nullable|string|max:1000',
                'admission_date' => 'required|date|before_or_equal:today',
                'ic_number' => 'nullable|string|max:20|unique:trainees,ic_number',
                
                // Guardian information
                'guardian_name' => 'required|string|max:255',
                'guardian_relationship' => 'required|string|max:100',
                'guardian_phone' => 'required|string|max:20',
                'guardian_email' => 'nullable|email',
                'guardian_address' => 'nullable|string|max:500',
                
                // Emergency contact
                'emergency_contact_name' => 'required|string|max:255',
                'emergency_contact_phone' => 'required|string|max:20',
                'emergency_contact_relationship' => 'required|string|max:100',
                
                // Consents
                'photo_consent' => 'boolean',
                'services_consent' => 'required|boolean',
                
                // Files
                'avatar' => 'nullable|image|max:2048',
                'documents.*' => 'file|max:5120',
                
                // Medical info
                'medical_allergies' => 'nullable|string|max:500',
                'medications' => 'nullable|string|max:500',
                'dietary_restrictions' => 'nullable|string|max:500',
                
                // Tags
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50'
            ], [
                'services_consent.required' => 'Services consent is required for registration.',
                'date_of_birth.before' => 'Date of birth must be in the past.',
                'admission_date.before_or_equal' => 'Admission date cannot be in the future.',
                'email.unique' => 'This email address is already registered.',
                'ic_number.unique' => 'This IC number is already registered.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();
            
            // Check for potential duplicates
            $duplicates = $this->traineeService->checkForDuplicates(
                $validated['name'],
                $validated['date_of_birth'],
                $validated['guardian_phone']
            );

            if ($duplicates->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'duplicate_warning' => true,
                    'duplicates' => $duplicates->map(function($trainee) {
                        return [
                            'id' => $trainee->id,
                            'name' => $trainee->name,
                            'date_of_birth' => $trainee->date_of_birth->format('Y-m-d'),
                            'guardian_phone' => $trainee->guardian_phone,
                            'status' => $trainee->status
                        ];
                    }),
                    'message' => 'Potential duplicate trainees found. Please verify this is not a duplicate registration.'
                ], 409);
            }

            DB::beginTransaction();
            
            // Handle avatar upload
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store(
                    'trainee_avatars/' . date('Y/m'), 
                    'public'
                );
            }
            
            // Prepare trainee data
            $traineeData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'condition' => $validated['condition'],
                'medical_history' => $validated['medical_history'],
                'admission_date' => $validated['admission_date'],
                'ic_number' => $validated['ic_number'],
                'avatar' => $avatarPath,
                'centre_id' => session('centre_id'),
                'status' => 'active',
                'photo_consent' => $validated['photo_consent'] ?? false,
                'services_consent' => $validated['services_consent'],
                
                // Structured data
                'guardian_info' => [
                    'guardian_name' => $validated['guardian_name'],
                    'guardian_relationship' => $validated['guardian_relationship'],
                    'guardian_phone' => $validated['guardian_phone'],
                    'guardian_email' => $validated['guardian_email'] ?? null,
                    'guardian_address' => $validated['guardian_address'] ?? null
                ],
                
                'emergency_contact' => [
                    'name' => $validated['emergency_contact_name'],
                    'phone' => $validated['emergency_contact_phone'],
                    'relationship' => $validated['emergency_contact_relationship']
                ],
                
                'medical_info' => [
                    'allergies' => $validated['medical_allergies'] ?? null,
                    'medications' => $validated['medications'] ?? null,
                    'dietary_restrictions' => $validated['dietary_restrictions'] ?? null
                ],
                
                'tags' => $validated['tags'] ?? []
            ];
            
            // Create trainee using service
            $trainee = $this->traineeService->createTrainee($traineeData);
            
            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $key => $document) {
                    $path = $document->store(
                        'trainee_documents/' . $trainee->id, 
                        'public'
                    );
                    
                    TraineeDocument::create([
                        'trainee_id' => $trainee->id,
                        'document_type' => $document->getClientOriginalExtension(),
                        'document_name' => $document->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $document->getSize(),
                        'uploaded_by' => session('id')
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Trainee registered successfully',
                'trainee' => [
                    'id' => $trainee->id,
                    'name' => $trainee->name,
                    'unique_identifier' => $trainee->unique_identifier
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            // Clean up uploaded files on error
            if (isset($avatarPath) && $avatarPath) {
                Storage::disk('public')->delete($avatarPath);
            }
            
            \Log::error('Trainee creation failed', [
                'user_id' => session('id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to register trainee: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Show trainee details
     */
    public function show($id)
    {
        try {
            $trainee = Trainee::with([
                'documents',
                'progress.assessor',
                'auditLogs.user',
                'lastUpdatedBy'
            ])->findOrFail($id);
            
            // Check centre access
            if ($trainee->centre_id !== session('centre_id') && session('role') !== 'admin') {
                abort(403, 'Access denied');
            }
            
            // Get progress statistics
            $progressStats = [
                'average_progress' => $trainee->average_progress,
                'latest_assessment' => $trainee->latest_progress,
                'assessments_count' => $trainee->progress()->count(),
                'needs_assessment' => $trainee->progress()
                    ->where('assessment_date', '>', now()->subMonths(3))
                    ->doesntExist()
            ];
            
            return view('trainees.enhanced-show', compact('trainee', 'progressStats'));
            
        } catch (\Exception $e) {
            \Log::error('Trainee show failed', [
                'trainee_id' => $id,
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Trainee not found or access denied.');
        }
    }
    
    /**
     * Bulk operations on multiple trainees
     */
    public function bulkOperation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'trainee_ids' => 'required|array|min:1',
                'trainee_ids.*' => 'integer|exists:trainees,id',
                'action' => 'required|in:activate,deactivate,graduate,transfer,export,delete,update_tags',
                'data' => 'array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $traineeIds = $request->trainee_ids;
            $action = $request->action;
            $data = $request->data ?? [];
            
            // Verify all trainees belong to user's centre (unless admin)
            if (session('role') !== 'admin') {
                $invalidTrainees = Trainee::whereIn('id', $traineeIds)
                    ->where('centre_id', '!=', session('centre_id'))
                    ->count();
                    
                if ($invalidTrainees > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied to some trainees'
                    ], 403);
                }
            }
            
            switch ($action) {
                case 'export':
                    return $this->exportTrainees($traineeIds, $request->get('format', 'excel'));
                    
                case 'delete':
                    if (session('role') !== 'admin') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only administrators can delete trainees'
                        ], 403);
                    }
                    break;
            }
            
            // Perform bulk operation
            $updated = $this->traineeService->bulkUpdate($traineeIds, $action, $data);
            
            return response()->json([
                'success' => true,
                'message' => "Successfully {$action}d {$updated} trainee(s)",
                'updated_count' => $updated
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Bulk operation failed', [
                'action' => $request->action ?? 'unknown',
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Bulk operation failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export trainees to various formats
     */
    public function exportTrainees($traineeIds, $format = 'excel')
    {
        try {
            $trainees = Trainee::whereIn('id', $traineeIds)
                ->with(['documents', 'progress'])
                ->get();
                
            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($trainees);
                case 'pdf':
                    return $this->exportToPdf($trainees);
                default:
                    return $this->exportToExcel($trainees);
            }
            
        } catch (\Exception $e) {
            \Log::error('Export failed', [
                'format' => $format,
                'trainee_count' => count($traineeIds),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export to CSV format
     */
    private function exportToCsv($trainees)
    {
        $filename = 'trainees_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($trainees) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Unique Identifier', 'Name', 'Email', 'Phone', 'Date of Birth',
                'Gender', 'Condition', 'Status', 'Admission Date', 'Guardian Name',
                'Guardian Phone', 'Emergency Contact', 'Emergency Phone'
            ]);
            
            // CSV data
            foreach ($trainees as $trainee) {
                fputcsv($file, [
                    $trainee->id,
                    $trainee->unique_identifier,
                    $trainee->name,
                    $trainee->email,
                    $trainee->phone,
                    $trainee->date_of_birth?->format('Y-m-d'),
                    $trainee->gender,
                    $trainee->condition,
                    $trainee->status,
                    $trainee->admission_date?->format('Y-m-d'),
                    $trainee->guardian_info['guardian_name'] ?? '',
                    $trainee->guardian_info['guardian_phone'] ?? '',
                    $trainee->emergency_contact['name'] ?? '',
                    $trainee->emergency_contact['phone'] ?? ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Export to Excel format (placeholder)
     */
    private function exportToExcel($trainees)
    {
        // For now, return CSV. In production, use PhpSpreadsheet or Laravel Excel
        return $this->exportToCsv($trainees);
    }
    
    /**
     * Export to PDF format (placeholder)
     */
    private function exportToPdf($trainees)
    {
        return response()->json([
            'success' => false,
            'message' => 'PDF export functionality coming soon'
        ]);
    }
    
    /**
     * Get trainee statistics
     */
    public function getStatistics()
    {
        try {
            $stats = $this->traineeService->getStatistics(session('centre_id'));
            
            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics'
            ], 500);
        }
    }
    
    /**
     * Search trainees (AJAX endpoint)
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $limit = min($request->get('limit', 10), 50);
            
            $trainees = Trainee::where('centre_id', session('centre_id'))
                ->search($query)
                ->select(['id', 'unique_identifier', 'trainee_first_name', 'trainee_last_name', 'status'])
                ->limit($limit)
                ->get()
                ->map(function($trainee) {
                    return [
                        'id' => $trainee->id,
                        'unique_identifier' => $trainee->unique_identifier,
                        'name' => $trainee->name,
                        'status' => $trainee->status
                    ];
                });
                
            return response()->json([
                'success' => true,
                'trainees' => $trainees
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed'
            ], 500);
        }
    }
    
    /**
     * Force create trainee even with duplicates (admin only)
     */
    public function forceCreate(Request $request)
    {
        if (session('role') !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can force create duplicates'
            ], 403);
        }
        
        // Set a flag to bypass duplicate checking
        $request->merge(['force_create' => true]);
        
        return $this->store($request);
    }
}