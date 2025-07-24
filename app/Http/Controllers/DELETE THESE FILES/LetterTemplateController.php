<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LetterTemplateController extends Controller
{
    /**
     * Store a new letter template from profile
     */
    public function store(Request $request)
    {
        try {
            Log::info('Letter template store initiated', [
                'user_id' => session('id'),
                'role' => session('role'),
                'data' => $request->except(['header_image', 'footer_image'])
            ]);

            // Validate input
            $validated = $request->validate([
                'template_name' => 'required|string|max:255',
                'template_content' => 'nullable|string',
                'header_content' => 'nullable|string|max:1000',
                'footer_content' => 'nullable|string|max:1000',
                'header_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'footer_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'template_name.required' => 'Template name is required.',
                'header_image.image' => 'Header image must be a valid image file.',
                'header_image.mimes' => 'Header image must be JPEG, PNG, JPG, or GIF.',
                'header_image.max' => 'Header image size must not exceed 2MB.',
                'footer_image.image' => 'Footer image must be a valid image file.',
                'footer_image.mimes' => 'Footer image must be JPEG, PNG, JPG, or GIF.',
                'footer_image.max' => 'Footer image size must not exceed 2MB.',
            ]);

            DB::beginTransaction();

            // Handle image uploads
            $headerImagePath = null;
            $footerImagePath = null;
            
            if ($request->hasFile('header_image')) {
                $headerImage = $request->file('header_image');
                $headerImageName = 'header_' . time() . '_' . uniqid() . '.' . $headerImage->getClientOriginalExtension();
                $headerImagePath = $headerImage->storeAs('template_images', $headerImageName, 'public');
                
                Log::info('Header image uploaded', [
                    'original_name' => $headerImage->getClientOriginalName(),
                    'stored_path' => $headerImagePath,
                    'user_id' => session('id')
                ]);
            }
            
            if ($request->hasFile('footer_image')) {
                $footerImage = $request->file('footer_image');
                $footerImageName = 'footer_' . time() . '_' . uniqid() . '.' . $footerImage->getClientOriginalExtension();
                $footerImagePath = $footerImage->storeAs('template_images', $footerImageName, 'public');
                
                Log::info('Footer image uploaded', [
                    'original_name' => $footerImage->getClientOriginalName(),
                    'stored_path' => $footerImagePath,
                    'user_id' => session('id')
                ]);
            }

            // Build template content from header and footer content
            $templateContent = '';
            if (!empty($validated['header_content'])) {
                $templateContent .= '<div class="header-content">' . $validated['header_content'] . '</div>';
            }
            $templateContent .= '<div class="main-content">[CONTENT]</div>';
            if (!empty($validated['footer_content'])) {
                $templateContent .= '<div class="footer-content">' . $validated['footer_content'] . '</div>';
            }

            // Deactivate previous templates
            LetterTemplate::where('is_active', true)->update(['is_active' => false]);

            // Create new template
            $template = LetterTemplate::create([
                'template_name' => $validated['template_name'],
                'template_content' => $templateContent ?: '<div class="main-content">[CONTENT]</div>',
                'template_type' => 'letter',
                'template_variables' => [
                    'header_content' => $validated['header_content'] ?? '',
                    'footer_content' => $validated['footer_content'] ?? '',
                    'header_image' => $headerImagePath,
                    'footer_image' => $footerImagePath,
                ],
                'created_by' => session('id'),
                'is_active' => true,
            ]);

            DB::commit();

            Log::info('Letter template created successfully', [
                'template_id' => $template->id,
                'user_id' => session('id')
            ]);

            return redirect()->back()->with('success', 'Letter template saved successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('Letter template validation failed', [
                'errors' => $e->errors(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Letter template save error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id'),
                'input' => $request->except(['header_image', 'footer_image'])
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to save letter template. Please check the logs for more information.')
                ->withInput();
        }
    }

    /**
     * Generate a new letter from profile with PDF
     */
    public function generate(Request $request)
    {
        try {
            Log::info('Letter generation initiated from profile', [
                'user_id' => session('id'),
                'role' => session('role')
            ]);

            $validated = $request->validate([
                'reference_number' => 'required|string|max:50|unique:letters,letter_reference',
                'letter_date' => 'required|date',
                'subject' => 'required|string|max:255',
                'content' => 'required|string',
                'recipient_name' => 'nullable|string|max:255',
                'recipient_address' => 'nullable|string',
                'recipient_id' => 'nullable|integer',
                'recipient_type' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();

            // Get active template
            $template = LetterTemplate::getActive();
            if (!$template) {
                throw new \Exception('No active letter template found. Please create a template first.');
            }

            // Get user details
            $user = Users::find(session('id'));
            if (!$user) {
                throw new \Exception('User not found');
            }

            // Create letter record
            $letter = Letter::create([
                'letter_reference' => $validated['reference_number'],
                'letter_date' => $validated['letter_date'],
                'letter_subject' => $validated['subject'],
                'letter_content' => $validated['content'],
                'letter_type' => 'official',
                'recipient_id' => $validated['recipient_id'] ?? 0, // Default to 0 if not provided
                'recipient_type' => $validated['recipient_type'] ?? 'external',
                'template_id' => $template->id,
                'letter_status' => 'draft',
                'created_by' => session('id'),
                'letter_data' => [
                    'generated_by_name' => session('name') ?? $user->name,
                    'generated_by_position' => $user->position ?? ucfirst(session('role')),
                    'recipient_name' => $validated['recipient_name'] ?? 'Unknown',
                    'recipient_address' => $validated['recipient_address'] ?? '',
                ]
            ]);

            // Generate PDF
            $pdfPath = $this->generatePDF($letter, $template);
            $letter->update(['letter_file_path' => $pdfPath]);

            DB::commit();

            Log::info('Letter generated successfully with PDF', [
                'letter_id' => $letter->id,
                'reference' => $letter->letter_reference,
                'letter_file_path' => $pdfPath
            ]);

            // Generate URLs for the PDF
            $publicPdfUrl = asset('letters/' . basename($pdfPath));
            $downloadUrl = route('profile.letter.download', $letter->id);
            
            // Option to immediately download the PDF
            $immediateDownload = $request->get('immediate_download', false);
            
            if ($immediateDownload) {
                // Return the PDF file directly for immediate download
                $publicPdfPath = public_path('letters/' . basename($pdfPath));
                if (file_exists($publicPdfPath)) {
                    return response()->download($publicPdfPath, $letter->letter_reference . '.pdf', [
                        'Content-Type' => 'application/pdf'
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Letter generated successfully with PDF',
                'reference_number' => $letter->letter_reference,
                'letter_id' => $letter->id,
                'pdf_url' => $publicPdfUrl,
                'download_url' => $downloadUrl,
                'storage_url' => Storage::url($pdfPath)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Letter generation error', [
                'message' => $e->getMessage(),
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
     * Preview letter before generation
     */
    public function preview(Request $request)
    {
        try {
            $validated = $request->validate([
                'subject' => 'required|string|max:255',
                'content' => 'required|string',
                'letter_date' => 'required|date',
                'recipient_name' => 'nullable|string|max:255',
                'recipient_address' => 'nullable|string'
            ]);

            $template = LetterTemplate::getActive();
            
            $html = view('letters.preview', [
                'template' => $template,
                'subject' => $validated['subject'],
                'content' => $validated['content'],
                'letter_date' => \Carbon\Carbon::parse($validated['letter_date'])->format('d F Y'),
                'recipient_name' => $validated['recipient_name'] ?? 'Recipient Name',
                'recipient_address' => $validated['recipient_address'] ?? ''
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Letter preview error', [
                'message' => $e->getMessage(),
                'user_id' => session('id')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate preview'
            ], 500);
        }
    }

    /**
     * Generate a new reference number
     */
    public function newReference()
    {
        try {
            $reference = Letter::generateReferenceNumber();
            
            return response()->json([
                'success' => true,
                'reference' => $reference
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating reference number', [
                'message' => $e->getMessage(),
                'user_id' => session('id')
            ]);
            
            return response()->json([
                'success' => false,
                'reference' => 'LTR/' . date('Y') . '/' . date('m') . '/0001'
            ]);
        }
    }

    /**
     * Generate PDF from letter data
     */
    private function generatePDF($letter, $template)
    {
        try {
            // Check if GD extension is available
            $gdAvailable = extension_loaded('gd');
            
            if (!$gdAvailable) {
                Log::warning('PHP GD extension not available, using fallback PDF generation');
            }
            
            // Prepare data for PDF view
            $data = [
                'letter' => $letter,
                'template' => $template,
                'gd_available' => $gdAvailable
            ];

            // Generate PDF with enhanced options for better compatibility
            $pdf = Pdf::loadView('letters.pdf-template', $data);
            $pdf->setPaper('A4', 'portrait');
            
            // Configure PDF options for better compatibility without GD
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 150,
                'fontHeightRatio' => 1.0,
                'isRemoteEnabled' => false, // Disable remote content to avoid GD dependency
                'chroot' => public_path(), // Restrict file access
                'debugKeepTemp' => false,
                'debugCss' => false,
                'debugLayout' => false,
                'debugLayoutLines' => false,
                'debugLayoutBlocks' => false,
                'debugLayoutInline' => false,
                'debugLayoutPaddingBox' => false,
                'enableCssFloat' => true,
                'enableHtml5Parser' => true
            ]);
            
            // Create filename with requested format: LTR_YYYY_MM_<reference>_<timestamp>.pdf
            $date = Carbon::parse($letter->letter_date);
            $cleanReference = str_replace(['/', ' ', '-'], '_', $letter->letter_reference);
            $timestamp = time();
            $filename = "LTR_{$date->format('Y')}_{$date->format('m')}_{$cleanReference}_{$timestamp}.pdf";
            
            // Storage paths
            $storagePath = 'letters/' . $filename;
            $publicPath = public_path('letters/' . $filename);
            
            // Ensure directories exist
            Storage::makeDirectory('public/letters');
            if (!file_exists(public_path('letters'))) {
                mkdir(public_path('letters'), 0755, true);
            }
            
            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }
            
            // Generate PDF content
            $pdfContent = $pdf->output();
            
            // Save to storage (for internal use)
            Storage::put('public/' . $storagePath, $pdfContent);
            
            // Also save directly to public/letters (for direct access)
            file_put_contents($publicPath, $pdfContent);
            
            Log::info('PDF generated successfully', [
                'storage_path' => $storagePath,
                'public_path' => $publicPath,
                'letter_id' => $letter->id,
                'gd_available' => $gdAvailable,
                'file_size' => strlen($pdfContent)
            ]);
            
            return $storagePath;
        } catch (\Exception $e) {
            Log::error('PDF generation error: ' . $e->getMessage(), [
                'letter_id' => $letter->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            // If PDF generation fails, provide a fallback HTML version
            return $this->createFallbackHtmlLetter($letter, $template);
        }
    }
    
    /**
     * Create fallback HTML letter if PDF generation fails
     */
    private function createFallbackHtmlLetter($letter, $template)
    {
        try {
            $date = Carbon::parse($letter->letter_date);
            $cleanReference = str_replace(['/', ' ', '-'], '_', $letter->letter_reference);
            $timestamp = time();
            $filename = "LTR_{$date->format('Y')}_{$date->format('m')}_{$cleanReference}_{$timestamp}.html";
            
            $data = [
                'letter' => $letter,
                'template' => $template,
                'fallback_mode' => true
            ];
            
            $htmlContent = view('letters.pdf-template', $data)->render();
            
            // Wrap in complete HTML document
            $fullHtmlContent = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Letter - {$letter->letter_reference}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .letter-container { max-width: 800px; margin: 0 auto; }
        .fallback-notice { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class='letter-container'>
        <div class='fallback-notice'>
            <strong>Note:</strong> This letter was generated in HTML format as a fallback. 
            Please contact the system administrator for PDF generation issues.
        </div>
        {$htmlContent}
    </div>
</body>
</html>";
            
            $storagePath = 'letters/' . $filename;
            $publicPath = public_path('letters/' . $filename);
            
            Storage::put('public/' . $storagePath, $fullHtmlContent);
            file_put_contents($publicPath, $fullHtmlContent);
            
            Log::info('Fallback HTML letter created', [
                'storage_path' => $storagePath,
                'public_path' => $publicPath,
                'letter_id' => $letter->id
            ]);
            
            return $storagePath;
        } catch (\Exception $e) {
            Log::error('Fallback HTML generation also failed: ' . $e->getMessage());
            throw new \Exception('Both PDF and HTML generation failed. Please contact system administrator.');
        }
    }

    /**
     * View a letter PDF in browser
     */
    public function viewLetter($id)
    {
        try {
            $letter = Letter::findOrFail($id);
            
            // Check if user has access to this letter
            if (session('role') !== 'admin' && $letter->generated_by !== session('id')) {
                return redirect()->back()->with('error', 'Access denied to this letter.');
            }
            
            if (!$letter->letter_file_path) {
                return redirect()->back()->with('error', 'PDF not found for this letter.');
            }
            
            // Get the public PDF path
            $publicPdfPath = public_path('letters/' . basename($letter->letter_file_path));
            
            if (!file_exists($publicPdfPath)) {
                return redirect()->back()->with('error', 'PDF file not found.');
            }
            
            // Return the PDF file
            return response()->file($publicPdfPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($letter->letter_file_path) . '"'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error viewing letter PDF', [
                'letter_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()->with('error', 'Failed to view letter PDF.');
        }
    }

    /**
     * Download a letter PDF
     */
    public function downloadLetter($id)
    {
        try {
            $letter = Letter::findOrFail($id);
            
            // Check if user has access to this letter
            if (session('role') !== 'admin' && $letter->generated_by !== session('id')) {
                return redirect()->back()->with('error', 'Access denied to this letter.');
            }
            
            if (!$letter->letter_file_path) {
                return redirect()->back()->with('error', 'PDF not found for this letter.');
            }
            
            // Get the public PDF path
            $publicPdfPath = public_path('letters/' . basename($letter->letter_file_path));
            
            if (!file_exists($publicPdfPath)) {
                return redirect()->back()->with('error', 'PDF file not found.');
            }
            
            // Log the download
            Log::info('Letter PDF downloaded', [
                'letter_id' => $letter->id,
                'reference' => $letter->letter_reference,
                'downloaded_by' => session('id')
            ]);
            
            // Return the PDF as download
            return response()->download($publicPdfPath, basename($letter->letter_file_path), [
                'Content-Type' => 'application/pdf'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error downloading letter PDF', [
                'letter_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()->with('error', 'Failed to download letter PDF.');
        }
    }

    /**
     * Show all letters page
     */
    public function index(Request $request)
    {
        try {
            $query = Letter::with('template');
            
            // Filter by user if not admin
            if (session('role') !== 'admin') {
                $query->where('generated_by', session('id'));
            }
            
            // Apply search filter
            if ($request->filled('search')) {
                $query->search($request->search);
            }
            
            // Apply date range filter
            if ($request->filled('date_from')) {
                $query->where('letter_date', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->where('letter_date', '<=', $request->date_to);
            }
            
            $letters = $query->orderBy('created_at', 'desc')->paginate(15);
            
            Log::info('Letters index accessed', [
                'user_id' => session('id'),
                'role' => session('role'),
                'letters_count' => $letters->total(),
                'search_term' => $request->search,
                'date_filters' => [
                    'from' => $request->date_from,
                    'to' => $request->date_to
                ]
            ]);
            
            return view('letters.index', compact('letters'));
            
        } catch (\Exception $e) {
            Log::error('Error loading letters index', [
                'error' => $e->getMessage(),
                'user_id' => session('id'),
                'request_params' => $request->all()
            ]);
            
            return redirect()->route('profile')->with('error', 'Failed to load letters.');
        }
    }

    /**
     * Delete a letter
     */
    public function destroy($id)
    {
        try {
            $letter = Letter::findOrFail($id);
            
            // Check if user has permission to delete
            if (session('role') !== 'admin' && $letter->generated_by !== session('id')) {
                return redirect()->back()->with('error', 'Access denied to delete this letter.');
            }
            
            // Delete PDF files if they exist
            if ($letter->letter_file_path) {
                // Delete from storage
                if (Storage::exists('public/' . $letter->letter_file_path)) {
                    Storage::delete('public/' . $letter->letter_file_path);
                }
                
                // Delete from public directory
                $publicPdfPath = public_path('letters/' . basename($letter->letter_file_path));
                if (file_exists($publicPdfPath)) {
                    unlink($publicPdfPath);
                }
            }
            
            // Log the deletion
            Log::info('Letter deleted', [
                'letter_id' => $letter->id,
                'reference' => $letter->letter_reference,
                'deleted_by' => session('id')
            ]);
            
            // Delete the letter record
            $letter->delete();
            
            return redirect()->back()->with('success', 'Letter deleted successfully.');
            
        } catch (\Exception $e) {
            Log::error('Error deleting letter', [
                'letter_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()->with('error', 'Failed to delete letter.');
        }
    }

    /**
     * Show the form for creating a new letter
     */
    public function create()
    {
        try {
            $templates = LetterTemplate::where('is_active', true)->get();
            $users = Users::where('role', '!=', 'trainee')->get();
            
            return view('letters.create', compact('templates', 'users'));
            
        } catch (\Exception $e) {
            Log::error('Error loading letter create form', [
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('letters.index')->with('error', 'Failed to load create form.');
        }
    }

    /**
     * Show the specified letter
     */
    public function show($id)
    {
        try {
            $letter = Letter::with(['template', 'generatedBy'])->findOrFail($id);
            
            // Check if user has permission to view
            if (session('role') !== 'admin' && $letter->generated_by !== session('id')) {
                return redirect()->route('letters.index')->with('error', 'Access denied to view this letter.');
            }
            
            Log::info('Letter viewed', [
                'letter_id' => $letter->id,
                'reference' => $letter->letter_reference,
                'viewed_by' => session('id')
            ]);
            
            return view('letters.show', compact('letter'));
            
        } catch (\Exception $e) {
            Log::error('Error showing letter', [
                'letter_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('letters.index')->with('error', 'Letter not found.');
        }
    }

    /**
     * Show the form for editing the specified letter
     */
    public function edit($id)
    {
        try {
            $letter = Letter::with(['template'])->findOrFail($id);
            
            // Check if user has permission to edit
            if (session('role') !== 'admin' && $letter->generated_by !== session('id')) {
                return redirect()->route('letters.index')->with('error', 'Access denied to edit this letter.');
            }
            
            $templates = LetterTemplate::where('is_active', true)->get();
            $users = Users::where('role', '!=', 'trainee')->get();
            
            return view('letters.edit', compact('letter', 'templates', 'users'));
            
        } catch (\Exception $e) {
            Log::error('Error loading letter edit form', [
                'letter_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('letters.index')->with('error', 'Failed to load edit form.');
        }
    }

    /**
     * Update the specified letter
     */
    public function update(Request $request, $id)
    {
        try {
            $letter = Letter::findOrFail($id);
            
            // Check if user has permission to update
            if (session('role') !== 'admin' && $letter->generated_by !== session('id')) {
                return redirect()->route('letters.index')->with('error', 'Access denied to update this letter.');
            }

            $validated = $request->validate([
                'recipient_name' => 'required|string|max:255',
                'recipient_address' => 'required|string|max:500',
                'subject' => 'required|string|max:255',
                'body_content' => 'required|string',
                'letter_date' => 'required|date',
                'template_id' => 'nullable|exists:letter_templates,id',
                'additional_data' => 'nullable|json'
            ]);

            DB::beginTransaction();

            $letter->update([
                'recipient_name' => $validated['recipient_name'],
                'recipient_address' => $validated['recipient_address'],
                'subject' => $validated['subject'],
                'body_content' => $validated['body_content'],
                'letter_date' => $validated['letter_date'],
                'template_id' => $validated['template_id'],
                'additional_data' => $validated['additional_data'] ? json_decode($validated['additional_data'], true) : null,
                'updated_at' => now()
            ]);

            DB::commit();

            Log::info('Letter updated', [
                'letter_id' => $letter->id,
                'reference' => $letter->letter_reference,
                'updated_by' => session('id')
            ]);

            return redirect()->route('letters.show', $letter->id)->with('success', 'Letter updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating letter', [
                'letter_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => session('id'),
                'data' => $request->all()
            ]);

            return redirect()->back()->withInput()->with('error', 'Failed to update letter.');
        }
    }
}