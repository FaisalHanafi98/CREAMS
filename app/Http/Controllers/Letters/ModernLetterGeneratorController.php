<?php

namespace App\Http\Controllers\Letters;

use App\Http\Controllers\Controller;
use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Centre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;

class ModernLetterGeneratorController extends Controller
{
    /**
     * Modern Letter Generator Dashboard
     */
    public function index()
    {
        try {
            // Get recent letters
            $recentLetters = Letter::with(['template', 'creator'])
                ->where('created_by', session('id'))
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Get active templates
            $templates = LetterTemplate::where('is_active', true)
                ->orderBy('template_name')
                ->get();

            // Get statistics
            $stats = [
                'total_letters' => Letter::where('created_by', session('id'))->count(),
                'this_month' => Letter::where('created_by', session('id'))
                    ->whereMonth('created_at', now()->month)
                    ->count(),
                'templates_available' => $templates->count(),
                'pending_letters' => Letter::where('created_by', session('id'))
                    ->where('letter_status', 'draft')
                    ->count()
            ];

            return view('letters.modern.dashboard', compact('recentLetters', 'templates', 'stats'));
        } catch (Exception $e) {
            Log::error('Error loading letter generator dashboard: ' . $e->getMessage());
            return back()->with('error', 'Unable to load letter generator dashboard.');
        }
    }

    /**
     * Show letter creation wizard
     */
    public function create(Request $request)
    {
        try {
            $templateId = $request->get('template_id');
            $template = null;
            
            if ($templateId) {
                $template = LetterTemplate::findOrFail($templateId);
            }

            // Get available templates
            $templates = LetterTemplate::where('is_active', true)
                ->orderBy('template_name')
                ->get();

            // Get trainees for recipient selection
            $trainees = Trainee::with(['guardian'])
                ->where('centre_id', session('centre_id'))
                ->where('status', 'active')
                ->orderBy('trainee_first_name')
                ->get();

            // Get centres
            $centres = Centre::where('centre_status', 'active')
                ->orderBy('centre_name')
                ->get();

            return view('letters.modern.create', compact('template', 'templates', 'trainees', 'centres'));
        } catch (Exception $e) {
            Log::error('Error loading letter creation form: ' . $e->getMessage());
            return back()->with('error', 'Unable to load letter creation form.');
        }
    }

    /**
     * Generate letter with modern processing
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:letter_templates,id',
            'letter_subject' => 'required|string|max:255',
            'recipient_type' => 'required|in:trainee,guardian,external,staff',
            'recipient_id' => 'nullable|integer',
            'recipient_name' => 'required|string|max:255',
            'recipient_address' => 'required|string|max:1000',
            'recipient_email' => 'nullable|email',
            'recipient_phone' => 'nullable|string|max:20',
            'letter_content' => 'required|string',
            'custom_variables' => 'nullable|array',
            'letter_priority' => 'required|in:normal,urgent,high',
            'delivery_method' => 'required|in:email,print,both',
            'send_copy_to' => 'nullable|string',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $template = LetterTemplate::findOrFail($validated['template_id']);
            
            // Generate unique reference number
            $reference = $this->generateReferenceNumber($template->template_category);
            
            // Process template variables
            $processedContent = $this->processTemplateVariables(
                $validated['letter_content'], 
                $validated['custom_variables'] ?? [],
                $validated
            );

            // Create letter record
            $letter = Letter::create([
                'letter_id' => $reference,
                'template_id' => $validated['template_id'],
                'letter_subject' => $validated['letter_subject'],
                'letter_content' => $processedContent,
                'letter_data' => json_encode([
                    'recipient_type' => $validated['recipient_type'],
                    'recipient_id' => $validated['recipient_id'],
                    'recipient_name' => $validated['recipient_name'],
                    'recipient_address' => $validated['recipient_address'],
                    'recipient_email' => $validated['recipient_email'],
                    'recipient_phone' => $validated['recipient_phone'],
                    'priority' => $validated['letter_priority'],
                    'delivery_method' => $validated['delivery_method'],
                    'send_copy_to' => $validated['send_copy_to'],
                    'notes' => $validated['notes'],
                    'custom_variables' => $validated['custom_variables'] ?? []
                ]),
                'created_by' => session('id'),
                'generated_by' => session('id'),
                'centre_id' => session('centre_id'),
                'letter_status' => 'generated',
                'generated_at' => now()
            ]);

            // Generate PDF
            $pdfPath = $this->generatePDF($letter, $processedContent);
            $letter->update(['pdf_path' => $pdfPath]);

            // Handle delivery
            if ($validated['delivery_method'] === 'email' || $validated['delivery_method'] === 'both') {
                $this->sendEmailDelivery($letter);
            }

            DB::commit();

            Log::info('Letter generated successfully', [
                'letter_id' => $letter->id,
                'reference' => $reference,
                'created_by' => session('id')
            ]);

            return redirect()->route('letters.modern.show', $letter->id)
                ->with('success', 'Letter generated successfully!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error generating letter: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate letter. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show generated letter
     */
    public function show($id)
    {
        try {
            $letter = Letter::with(['template', 'creator'])->findOrFail($id);
            
            // Check permissions
            if (session('role') !== 'admin' && $letter->created_by !== session('id')) {
                return redirect()->route('letters.modern.index')
                    ->with('error', 'You do not have permission to view this letter.');
            }

            $letterData = json_decode($letter->letter_data, true);
            
            return view('letters.modern.show', compact('letter', 'letterData'));
        } catch (Exception $e) {
            Log::error('Error loading letter: ' . $e->getMessage());
            return back()->with('error', 'Unable to load letter.');
        }
    }

    /**
     * Download letter PDF
     */
    public function downloadPDF($id)
    {
        try {
            $letter = Letter::findOrFail($id);
            
            // Check permissions
            if (session('role') !== 'admin' && $letter->created_by !== session('id')) {
                abort(403, 'Unauthorized access');
            }

            if ($letter->pdf_path && Storage::exists($letter->pdf_path)) {
                return Storage::download($letter->pdf_path, $letter->letter_id . '.pdf');
            } else {
                // Regenerate PDF if missing
                $pdfPath = $this->generatePDF($letter, $letter->letter_content);
                $letter->update(['pdf_path' => $pdfPath]);
                
                return Storage::download($pdfPath, $letter->letter_id . '.pdf');
            }
        } catch (Exception $e) {
            Log::error('Error downloading letter PDF: ' . $e->getMessage());
            return back()->with('error', 'Unable to download letter PDF.');
        }
    }

    /**
     * Get template preview
     */
    public function getTemplatePreview($templateId)
    {
        try {
            $template = LetterTemplate::findOrFail($templateId);
            
            // Process template with sample data
            $sampleData = [
                'recipient_name' => 'John Doe',
                'recipient_address' => '123 Sample Street, Sample City',
                'current_date' => now()->format('F j, Y'),
                'centre_name' => session('centre_name', 'CREAMS Centre'),
                'sender_name' => session('name', 'Staff Member')
            ];
            
            $processedContent = $this->processTemplateVariables(
                $template->template_content,
                [],
                $sampleData
            );
            
            return response()->json([
                'success' => true,
                'content' => $processedContent,
                'variables' => $template->template_variables ? json_decode($template->template_variables, true) : []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load template preview'
            ], 500);
        }
    }

    /**
     * Archive letter
     */
    public function archive($id)
    {
        try {
            $letter = Letter::findOrFail($id);
            
            // Check permissions
            if (session('role') !== 'admin' && $letter->created_by !== session('id')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $letter->update(['letter_status' => 'archived']);
            
            return response()->json(['success' => true, 'message' => 'Letter archived successfully']);
        } catch (Exception $e) {
            Log::error('Error archiving letter: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to archive letter'], 500);
        }
    }

    /**
     * Search letters
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $category = $request->get('category', 'all');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            
            $letters = Letter::with(['template', 'creator'])
                ->where('created_by', session('id'))
                ->where(function($q) use ($query) {
                    $q->where('letter_id', 'LIKE', "%{$query}%")
                      ->orWhere('letter_subject', 'LIKE', "%{$query}%")
                      ->orWhere('letter_content', 'LIKE', "%{$query}%");
                })
                ->when($category !== 'all', function($q) use ($category) {
                    $q->whereHas('template', function($subQ) use ($category) {
                        $subQ->where('template_category', $category);
                    });
                })
                ->when($dateFrom, function($q) use ($dateFrom) {
                    $q->whereDate('created_at', '>=', $dateFrom);
                })
                ->when($dateTo, function($q) use ($dateTo) {
                    $q->whereDate('created_at', '<=', $dateTo);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'letters' => $letters->items(),
                'pagination' => [
                    'current_page' => $letters->currentPage(),
                    'last_page' => $letters->lastPage(),
                    'total' => $letters->total()
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Error searching letters: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Search failed'], 500);
        }
    }

    /**
     * Generate unique reference number
     */
    private function generateReferenceNumber($category = 'GEN'): string
    {
        $prefix = strtoupper(substr($category, 0, 3));
        $centreCode = session('centre_id', '01');
        $year = date('Y');
        $month = date('m');
        
        // Get next sequence number
        $lastLetter = Letter::where('letter_id', 'LIKE', "{$prefix}-{$centreCode}-{$year}{$month}-%")
            ->orderBy('letter_id', 'desc')
            ->first();
            
        $sequence = 1;
        if ($lastLetter) {
            $lastSequence = (int) substr($lastLetter->letter_id, -4);
            $sequence = $lastSequence + 1;
        }
        
        return sprintf('%s-%s-%s%s-%04d', $prefix, $centreCode, $year, $month, $sequence);
    }

    /**
     * Process template variables
     */
    private function processTemplateVariables(string $content, array $customVars, array $data): string
    {
        $variables = array_merge([
            '{{recipient_name}}' => $data['recipient_name'] ?? '',
            '{{recipient_address}}' => $data['recipient_address'] ?? '',
            '{{current_date}}' => now()->format('F j, Y'),
            '{{centre_name}}' => session('centre_name', 'CREAMS Centre'),
            '{{centre_address}}' => $this->getCentreAddress(),
            '{{sender_name}}' => session('name', 'Staff Member'),
            '{{sender_position}}' => session('position', 'Staff'),
            '{{letter_subject}}' => $data['letter_subject'] ?? ''
        ], $customVars);
        
        return str_replace(array_keys($variables), array_values($variables), $content);
    }

    /**
     * Generate PDF from letter content
     */
    private function generatePDF($letter, $content): string
    {
        $pdf = Pdf::loadView('letters.pdf.template', [
            'letter' => $letter,
            'content' => $content,
            'letterData' => json_decode($letter->letter_data, true)
        ]);
        
        $filename = 'letters/' . $letter->letter_id . '.pdf';
        Storage::put($filename, $pdf->output());
        
        return $filename;
    }

    /**
     * Send email delivery
     */
    private function sendEmailDelivery($letter)
    {
        // Email delivery implementation would go here
        // For now, just log the action
        Log::info('Email delivery requested for letter: ' . $letter->letter_id);
    }

    /**
     * Get centre address
     */
    private function getCentreAddress(): string
    {
        $centreId = session('centre_id');
        $centre = Centre::find($centreId);
        
        return $centre ? $centre->centre_address : 'Centre Address';
    }
}