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

class LetterController extends Controller
{
    public function __construct()
    {
        // Ensure only admins can access
        $this->middleware(function ($request, $next) {
            if (session('role') !== 'admin') {
                return redirect()->route('dashboard')->with('error', 'Unauthorized access');
            }
            return $next($request);
        });
    }

    /**
     * Show letters archive page
     */
    public function index(Request $request)
    {
        try {
            $query = Letter::with('template');
            
            // Admins can see all letters, others see only their own
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
            
            Log::info('Admin letters index accessed', [
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
            Log::error('Letter index error: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Failed to load letter archive');
        }
    }

    /**
     * Upload/Update template
     */
    public function updateTemplate(Request $request)
    {
        $request->validate([
            'header_image' => 'nullable|image|max:2048',
            'footer_image' => 'nullable|image|max:2048', 
            'header_content' => 'nullable|string|max:1000',
            'footer_content' => 'nullable|string|max:1000',
            'template_name' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Deactivate all existing templates
            LetterTemplate::where('is_active', true)->update(['is_active' => false]);

            $template = new LetterTemplate();
            $template->template_name = $request->template_name ?? 'Template ' . date('Y-m-d H:i:s');
            $template->created_by = session('id');

            // Handle header image upload
            if ($request->hasFile('header_image')) {
                $headerPath = $request->file('header_image')->store('letter-templates/headers', 'public');
                $template->header_image = $headerPath;
            }

            // Handle footer image upload
            if ($request->hasFile('footer_image')) {
                $footerPath = $request->file('footer_image')->store('letter-templates/footers', 'public');
                $template->footer_image = $footerPath;
            }

            $template->header_content = $request->header_content;
            $template->footer_content = $request->footer_content;
            $template->is_active = true;
            $template->save();

            DB::commit();

            Log::info('Letter template updated', [
                'admin_id' => session('id'),
                'template_id' => $template->id
            ]);

            return back()->with('success', 'Letter template updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Template update error: ' . $e->getMessage());
            return back()->with('error', 'Failed to update template: ' . $e->getMessage());
        }
    }

    /**
     * Generate new letter
     */
    public function generateLetter(Request $request)
    {
        $request->validate([
            'reference_number' => 'required|string|max:50|unique:letters,reference_number',
            'letter_date' => 'required|date',
            'recipient_name' => 'required|string|max:255',
            'recipient_address' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // Get active template
            $template = LetterTemplate::getActive();
            if (!$template) {
                throw new \Exception('No active letter template found. Please create a template first.');
            }

            // Get admin details
            $admin = Users::find(session('id'));
            if (!$admin) {
                throw new \Exception('Admin user not found');
            }

            // Create letter record
            $letter = Letter::create([
                'reference_number' => $request->reference_number,
                'letter_date' => $request->letter_date,
                'recipient_name' => $request->recipient_name,
                'recipient_address' => $request->recipient_address,
                'subject' => $request->subject,
                'content' => $request->content,
                'template_id' => $template->id,
                'generated_by' => $admin->id,
                'generated_by_name' => $admin->name,
                'generated_by_position' => $admin->position ?? 'Administrator'
            ]);

            // Generate PDF
            $pdfPath = $this->generatePDF($letter, $template);
            $letter->update(['pdf_path' => $pdfPath]);

            DB::commit();

            Log::info('Letter generated successfully', [
                'letter_id' => $letter->id,
                'reference' => $letter->reference_number,
                'admin_id' => $admin->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Letter generated successfully',
                'pdf_url' => Storage::url($pdfPath),
                'letter_id' => $letter->id,
                'reference_number' => $letter->reference_number
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Letter generation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate letter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF from letter data
     */
    private function generatePDF($letter, $template)
    {
        try {
            // Prepare data for PDF view
            $data = [
                'letter' => $letter,
                'template' => $template
            ];

            // Generate PDF
            $pdf = Pdf::loadView('letters.pdf-template', $data);
            $pdf->setPaper('A4', 'portrait');
            
            // Create filename with requested format: LTR_YYYY_MM_<reference>_<timestamp>.pdf
            $date = is_string($letter->letter_date) ? 
                \Carbon\Carbon::parse($letter->letter_date) : 
                $letter->letter_date;
            $cleanReference = str_replace(['/', ' ', '-'], '_', $letter->reference_number);
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
            
            // Generate PDF content
            $pdfContent = $pdf->output();
            
            // Save to storage (for internal use)
            Storage::put('public/' . $storagePath, $pdfContent);
            
            // Also save directly to public/letters (for direct access)
            file_put_contents($publicPath, $pdfContent);
            
            Log::info('PDF saved to both locations', [
                'storage_path' => $storagePath,
                'public_path' => $publicPath,
                'letter_id' => $letter->id
            ]);
            
            return $storagePath;
        } catch (\Exception $e) {
            Log::error('PDF generation error: ' . $e->getMessage());
            throw new \Exception('Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * View letter history
     */
    public function history(Request $request)
    {
        try {
            $query = Letter::with(['generator', 'template']);
            
            // Apply search filter
            if ($request->has('search') && $request->search) {
                $query->search($request->search);
            }
            
            // Apply date range filter
            if ($request->has('start_date') && $request->start_date) {
                $query->where('letter_date', '>=', $request->start_date);
            }
            
            if ($request->has('end_date') && $request->end_date) {
                $query->where('letter_date', '<=', $request->end_date);
            }
            
            // Apply generator filter (admin only sees their own by default)
            if ($request->has('show_all') && $request->show_all && session('role') === 'admin') {
                // Show all letters if explicitly requested and user is admin
            } else {
                $query->where('generated_by', session('id'));
            }
            
            $letters = $query->latest()->paginate(20);
            
            return view('profile.letter-history', compact('letters'));
        } catch (\Exception $e) {
            Log::error('Letter history error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load letter history');
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
            
            if (!$letter->pdf_path) {
                return redirect()->back()->with('error', 'PDF not found for this letter.');
            }
            
            // Get the public PDF path
            $publicPdfPath = public_path('letters/' . basename($letter->pdf_path));
            
            if (!file_exists($publicPdfPath)) {
                return redirect()->back()->with('error', 'PDF file not found.');
            }
            
            // Return the PDF file
            return response()->file($publicPdfPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($letter->pdf_path) . '"'
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
     * Download letter PDF
     */
    public function download($id)
    {
        try {
            $letter = Letter::findOrFail($id);
            
            // Check if user can access this letter
            if ($letter->generated_by !== session('id') && session('role') !== 'admin') {
                return abort(403, 'Unauthorized access to this letter');
            }
            
            if (!$letter->pdf_path) {
                return back()->with('error', 'PDF not found for this letter.');
            }
            
            // Try public directory first, then storage
            $publicPdfPath = public_path('letters/' . basename($letter->pdf_path));
            
            if (file_exists($publicPdfPath)) {
                // Log the download
                Log::info('Letter PDF downloaded from public directory', [
                    'letter_id' => $letter->id,
                    'reference' => $letter->reference_number,
                    'downloaded_by' => session('id')
                ]);
                
                return response()->download($publicPdfPath, basename($letter->pdf_path), [
                    'Content-Type' => 'application/pdf'
                ]);
            } elseif (Storage::exists('public/' . $letter->pdf_path)) {
                // Fallback to storage system
                $filename = str_replace(['/', ' '], ['_', '_'], $letter->reference_number) . '.pdf';
                return Storage::download('public/' . $letter->pdf_path, $filename);
            } else {
                return back()->with('error', 'PDF file not found. Please regenerate the letter.');
            }
        } catch (\Exception $e) {
            Log::error('Letter download error: ' . $e->getMessage());
            return back()->with('error', 'Failed to download letter');
        }
    }

    /**
     * Preview letter before generating PDF
     */
    public function preview(Request $request)
    {
        $request->validate([
            'recipient_name' => 'required|string|max:255',
            'recipient_address' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'letter_date' => 'required|date'
        ]);

        try {
            $template = LetterTemplate::getActive();
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active template found'
                ]);
            }

            $admin = Users::find(session('id'));
            
            // Create a temporary letter object for preview
            $letter = new Letter([
                'reference_number' => 'PREVIEW-' . time(),
                'letter_date' => $request->letter_date,
                'recipient_name' => $request->recipient_name,
                'recipient_address' => $request->recipient_address,
                'subject' => $request->subject,
                'content' => $request->content,
                'generated_by_name' => $admin->name,
                'generated_by_position' => $admin->position ?? 'Administrator'
            ]);
            
            $html = view('letters.preview-template', compact('letter', 'template'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            Log::error('Letter preview error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate preview'
            ]);
        }
    }

    /**
     * Get new reference number
     */
    public function newReference()
    {
        try {
            $reference = Letter::generateReferenceNumber();
            return response()->json(['reference' => $reference]);
        } catch (\Exception $e) {
            Log::error('New reference error: ' . $e->getMessage());
            return response()->json(['reference' => 'LTR/' . date('Y/m') . '/0001']);
        }
    }

    /**
     * Delete a letter (admin only)
     */
    public function destroy($id)
    {
        try {
            $letter = Letter::findOrFail($id);
            
            // Check permissions
            if ($letter->generated_by !== session('id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Delete PDF file if exists
            if ($letter->pdf_path && Storage::exists('public/' . $letter->pdf_path)) {
                Storage::delete('public/' . $letter->pdf_path);
            }
            
            $letter->delete();
            
            Log::info('Letter deleted', [
                'letter_id' => $id,
                'reference' => $letter->reference_number,
                'admin_id' => session('id')
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Letter deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Letter deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete letter'
            ], 500);
        }
    }
}