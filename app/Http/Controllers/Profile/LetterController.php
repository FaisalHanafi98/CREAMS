<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;

use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Users;
use App\Models\Trainees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LetterController extends Controller
{
    /**
     * Main letter dashboard - combines archive and generation
     */
    public function dashboard(Request $request)
    {
        try {
            $query = Letter::with(['template']);
            
            // Role-based filtering
            if (session('role') !== 'admin') {
                $query->where('created_by', session('id'));
            }
            
            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('letter_reference', 'LIKE', "%{$search}%")
                      ->orWhere('letter_subject', 'LIKE', "%{$search}%")
                      ->orWhere('letter_data', 'LIKE', "%{$search}%");
                });
            }
            
            $letters = $query->orderBy('created_at', 'desc')->paginate(20);

            // Process letter data for display
            foreach ($letters as $letter) {
                $this->processLetterData($letter);
            }

            $templates = LetterTemplate::where('is_active', 1)->get();
            $trainees = Trainees::where('centre_id', session('centre_id'))
                ->select('id', 'trainee_first_name', 'trainee_last_name', 'trainee_email')
                ->orderBy('trainee_first_name')
                ->get();

            return view('letters.new-dashboard', compact('letters', 'templates', 'trainees'));
            
        } catch (\Exception $e) {
            Log::error('Error loading letter dashboard: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Failed to load letter dashboard');
        }
    }

    /**
     * Generate letter via AJAX
     */
    public function generate(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validate input
            $validated = $request->validate([
                'recipient_name' => 'required|string|max:255',
                'recipient_address' => 'nullable|string',
                'letter_subject' => 'required|string|max:255',
                'letter_content' => 'required|string',
                'letter_date' => 'required|date',
                'template_id' => 'nullable|exists:letter_templates,id'
            ]);

            // Get template
            $template = $request->template_id 
                ? LetterTemplate::find($request->template_id)
                : LetterTemplate::where('is_active', 1)->first();

            if (!$template) {
                // Create default template if none exists
                $template = $this->createDefaultTemplate();
            }

            // Generate reference number
            $reference = $this->generateUniqueReference();

            // Create letter record
            $letter = Letter::create([
                'letter_reference' => $reference,
                'letter_date' => $validated['letter_date'],
                'letter_subject' => $validated['letter_subject'],
                'letter_content' => $validated['letter_content'],
                'letter_type' => 'official',
                'recipient_type' => $request->recipient_type ?? 'external',
                'recipient_id' => $request->recipient_id ?? 0,
                'template_id' => $template->id,
                'letter_status' => 'final',
                'created_by' => session('id'),
                'letter_data' => json_encode([
                    'recipient_name' => $validated['recipient_name'],
                    'recipient_address' => $validated['recipient_address'] ?? '',
                    'generated_by_name' => session('name'),
                    'generated_by_position' => session('role'),
                    'centre_name' => $this->getCentreName()
                ])
            ]);

            // Generate PDF
            $pdfPath = $this->generateEnhancedPDF($letter, $template);
            $letter->update(['letter_file_path' => $pdfPath]);

            DB::commit();

            Log::info('Letter generated successfully', [
                'reference' => $reference,
                'user_id' => session('id')
            ]);

            // Return success with download URL
            return response()->json([
                'success' => true,
                'message' => 'Letter generated successfully',
                'reference_number' => $reference,
                'letter_id' => $letter->id,
                'download_url' => route('letters.download', $letter->id),
                'preview_url' => route('letters.view', $letter->id)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Letter generation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate letter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview letter content
     */
    public function preview(Request $request)
    {
        try {
            $validated = $request->validate([
                'recipient_name' => 'required|string|max:255',
                'recipient_address' => 'nullable|string',
                'letter_subject' => 'required|string|max:255',
                'letter_content' => 'required|string',
                'letter_date' => 'required|date'
            ]);

            $previewData = [
                'letter_date' => $validated['letter_date'],
                'letter_reference' => 'PREVIEW-' . date('Ymd'),
                'letter_subject' => $validated['letter_subject'],
                'letter_content' => $validated['letter_content'],
                'recipient_name' => $validated['recipient_name'],
                'recipient_address' => $validated['recipient_address'] ?? '',
                'generated_by_name' => session('name'),
                'generated_by_position' => session('role'),
                'centre_name' => $this->getCentreName()
            ];

            $html = view('letters.preview-content', compact('previewData'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Letter preview failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate preview'
            ], 500);
        }
    }

    /**
     * View letter in browser
     */
    public function view($id)
    {
        try {
            $letter = Letter::findOrFail($id);
            
            // Check permissions
            if (session('role') !== 'admin' && $letter->created_by !== session('id')) {
                abort(403, 'Unauthorized access');
            }

            if (!$letter->letter_file_path || !Storage::disk('public')->exists($letter->letter_file_path)) {
                abort(404, 'PDF file not found');
            }

            $filePath = storage_path('app/public/' . $letter->letter_file_path);
            return response()->file($filePath);

        } catch (\Exception $e) {
            Log::error('Error viewing letter: ' . $e->getMessage());
            abort(500, 'Error loading letter');
        }
    }

    /**
     * Download letter PDF
     */
    public function download($id)
    {
        try {
            $letter = Letter::findOrFail($id);
            
            // Check permissions
            if (session('role') !== 'admin' && $letter->created_by !== session('id')) {
                abort(403, 'Unauthorized access');
            }

            if (!$letter->letter_file_path || !Storage::disk('public')->exists($letter->letter_file_path)) {
                abort(404, 'PDF file not found');
            }

            $filePath = storage_path('app/public/' . $letter->letter_file_path);
            $filename = 'letter_' . $letter->letter_reference . '.pdf';

            return response()->download($filePath, $filename);

        } catch (\Exception $e) {
            Log::error('Error downloading letter: ' . $e->getMessage());
            abort(500, 'Error downloading letter');
        }
    }

    /**
     * Delete letter
     */
    public function destroy($id)
    {
        try {
            $letter = Letter::findOrFail($id);
            
            // Check permissions
            if (session('role') !== 'admin' && $letter->created_by !== session('id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Delete PDF file if exists
            if ($letter->letter_file_path && Storage::disk('public')->exists($letter->letter_file_path)) {
                Storage::disk('public')->delete($letter->letter_file_path);
            }

            $letter->delete();

            Log::info('Letter deleted', [
                'reference' => $letter->letter_reference,
                'deleted_by' => session('id')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Letter deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting letter: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete letter'
            ], 500);
        }
    }

    /**
     * Enhanced PDF generation with proper template handling
     */
    private function generateEnhancedPDF($letter, $template)
    {
        // Prepare template images
        $headerImage = null;
        $footerImage = null;

        if ($template->template_variables) {
            $variables = is_string($template->template_variables) 
                ? json_decode($template->template_variables, true) 
                : $template->template_variables;

            // Process header image
            if (!empty($variables['header_image'])) {
                $headerPath = storage_path('app/public/template_images/' . $variables['header_image']);
                if (file_exists($headerPath)) {
                    $type = pathinfo($headerPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($headerPath);
                    $headerImage = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }

            // Process footer image
            if (!empty($variables['footer_image'])) {
                $footerPath = storage_path('app/public/template_images/' . $variables['footer_image']);
                if (file_exists($footerPath)) {
                    $type = pathinfo($footerPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($footerPath);
                    $footerImage = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
        }

        // Prepare data for PDF
        $data = [
            'letter' => $letter,
            'template' => $template,
            'headerImage' => $headerImage,
            'footerImage' => $footerImage,
            'letterData' => json_decode($letter->letter_data, true)
        ];

        // Generate PDF with custom settings
        $pdf = Pdf::loadView('letters.enhanced-pdf-template', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'sans-serif',
            'isFontSubsettingEnabled' => true
        ]);

        // Create filename
        $filename = 'letter_' . str_replace(['/', ' '], ['_', '_'], $letter->letter_reference) . '_' . date('Ymd') . '.pdf';
        $path = 'letters/' . date('Y/m') . '/' . $filename;

        // Ensure directory exists
        Storage::disk('public')->makeDirectory('letters/' . date('Y/m'));
        
        // Save PDF
        $fullPath = storage_path('app/public/' . $path);
        $pdf->save($fullPath);

        return $path;
    }

    /**
     * Process letter data for consistent display
     */
    private function processLetterData(&$letter)
    {
        if (is_string($letter->letter_data)) {
            $letter->letter_data = json_decode($letter->letter_data, true);
        }

        if (!is_array($letter->letter_data)) {
            $letter->letter_data = [];
        }

        // Ensure all required fields exist
        $letter->letter_data = array_merge([
            'recipient_name' => 'Unknown',
            'recipient_address' => '',
            'generated_by_name' => 'System',
            'generated_by_position' => 'Admin'
        ], $letter->letter_data);
    }

    /**
     * Create default template if none exists
     */
    private function createDefaultTemplate()
    {
        return LetterTemplate::create([
            'template_name' => 'Default Template',
            'template_content' => 'Standard letter template',
            'template_type' => 'official',
            'is_active' => 1,
            'created_by' => session('id'),
            'template_variables' => json_encode([
                'header_image' => null,
                'footer_image' => null,
                'margin_top' => 20,
                'margin_bottom' => 20
            ])
        ]);
    }

    /**
     * Generate unique reference number
     */
    private function generateUniqueReference()
    {
        $prefix = 'LTR';
        $year = date('Y');
        $month = date('m');
        
        // Get last sequence number for this month
        $lastLetter = Letter::where('letter_reference', 'LIKE', "{$prefix}/{$year}/{$month}/%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastLetter) {
            $parts = explode('/', $lastLetter->letter_reference);
            $sequence = intval(end($parts)) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf("{$prefix}/{$year}/{$month}/%05d", $sequence);
    }

    /**
     * Get centre name for letter
     */
    private function getCentreName()
    {
        // You can fetch this from database or config
        $centres = [
            '01' => 'Main Rehabilitation Centre',
            '02' => 'North Branch Centre', 
            '03' => 'South Branch Centre'
        ];

        return $centres[session('centre_id')] ?? 'CREAMS Centre';
    }
}