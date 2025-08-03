<?php

namespace App\Http\Controllers\Letters;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\User;
use Exception;

// Use Dompdf directly for better control
use Dompdf\Dompdf;
use Dompdf\Options;

class ModernLetterController extends Controller
{
    /**
     * Show letter generation dashboard
     */
    public function dashboard()
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            $templates = LetterTemplate::where('is_active', true)->get();
            $recentLetters = Letter::where('created_by', session('id'))
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return view('letters.moderndashboard', compact('templates', 'recentLetters'));

        } catch (Exception $e) {
            Log::error('Letter dashboard error', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard')->with('error', 'Unable to load letter dashboard');
        }
    }

    /**
     * Generate new letter with modern architecture
     */
    public function generate(Request $request)
    {
        try {
            if (!session()->has('id')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Validate request
            $validated = $request->validate([
                'recipient_name' => 'required|string|max:255',
                'recipient_address' => 'nullable|string|max:500',
                'letter_subject' => 'required|string|max:255',
                'letter_content' => 'required|string',
                'template_id' => 'nullable|exists:letter_templates,id',
                'letter_type' => 'required|in:official,invitation,certificate,recommendation,other'
            ]);

            DB::beginTransaction();

            // Let the model generate the unique reference number automatically
            // Remove manual reference generation to avoid conflicts
            
            // Create letter record
            $letter = Letter::create([
                'letter_subject' => $validated['letter_subject'],
                'letter_content' => $validated['letter_content'],
                'letter_date' => now(),
                'letter_data' => json_encode([
                    'recipient_name' => $validated['recipient_name'],
                    'recipient_address' => $validated['recipient_address'] ?? '',
                    'letter_type' => $validated['letter_type'],
                    'sender_name' => session('name'),
                    'sender_role' => session('role'),
                    'centre_id' => session('centre_id')
                ]),
                'template_id' => $validated['template_id'],
                'created_by' => session('id')
            ]);

            // Generate PDF with new architecture
            $pdfPath = $this->generateModernPDF($letter, $validated);
            
            // Update letter with file path
            $letter->update(['letter_file_path' => $pdfPath]);

            DB::commit();

            Log::info('Letter generated successfully', [
                'letter_id' => $letter->id,
                'reference' => $letter->letter_reference,
                'user_id' => session('id')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Letter generated successfully',
                'letter_id' => $letter->id,
                'reference' => $letter->letter_reference,
                'download_url' => route('letters.download', $letter->id)
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Letter generation failed', [
                'error' => $e->getMessage(),
                'user_id' => session('id') ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate letter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF with modern architecture and proper header/footer handling
     */
    private function generateModernPDF($letter, $data)
    {
        try {
            // Get template if specified
            $template = null;
            if ($letter->template_id) {
                $template = LetterTemplate::find($letter->template_id);
            }

            // Prepare PDF data
            $pdfData = [
                'letter' => $letter,
                'data' => $data,
                'template' => $template,
                'header_content' => $this->getHeaderContent($template),
                'footer_content' => $this->getFooterContent($template),
                'header_image' => $this->getHeaderImage($template),
                'footer_image' => $this->getFooterImage($template),
                'generated_date' => now()->format('F j, Y'),
                'user_name' => session('name'),
                'user_role' => ucfirst(session('role')),
                'centre_name' => $this->getCentreName(session('centre_id'))
            ];

            // Generate HTML content
            $html = view('letters.modern-pdf-template', $pdfData)->render();

            // Configure Dompdf with proper settings
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('dpi', 150);
            $options->set('debugPng', true);
            $options->set('debugKeepTemp', true);
            $options->set('chroot', [public_path(), storage_path()]);

            // Create Dompdf instance
            $dompdf = new Dompdf($options);
            
            // Load HTML with base path
            $dompdf->loadHtml($html, 'UTF-8');
            
            // Set paper size and orientation
            $dompdf->setPaper('A4', 'portrait');
            
            // Render PDF
            $dompdf->render();

            // Generate filename
            $filename = $letter->letter_reference . '_' . time() . '.pdf';
            $filePath = 'letters/' . $filename;
            
            // Ensure letters directory exists
            if (!file_exists(public_path('letters'))) {
                mkdir(public_path('letters'), 0755, true);
            }

            // Save PDF to public directory
            $fullPath = public_path($filePath);
            file_put_contents($fullPath, $dompdf->output());

            Log::info('PDF generated successfully', [
                'file_path' => $filePath,
                'file_size' => filesize($fullPath),
                'letter_id' => $letter->id
            ]);

            return $filePath;

        } catch (Exception $e) {
            Log::error('PDF generation error', [
                'error' => $e->getMessage(),
                'letter_id' => $letter->id ?? 'unknown'
            ]);
            throw $e;
        }
    }

    /**
     * Get header content with fallback
     */
    private function getHeaderContent($template)
    {
        if ($template && !empty($template->header_content)) {
            return $template->header_content;
        }
        
        return '<div style="text-align: center; padding: 20px; border-bottom: 3px solid #32bdea;">
                    <h1 style="color: #32bdea; margin: 0; font-size: 24px;">CREAMS</h1>
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Community-based REhAbilitation Management System</p>
                </div>';
    }

    /**
     * Get footer content with fallback
     */
    private function getFooterContent($template)
    {
        if ($template && !empty($template->footer_content)) {
            return $template->footer_content;
        }
        
        return '<div style="text-align: center; padding: 15px; border-top: 2px solid #e0e0e0; font-size: 12px; color: #666;">
                    <p style="margin: 0;">This is a computer-generated letter from CREAMS</p>
                    <p style="margin: 5px 0 0 0;">Generated on ' . now()->format('F j, Y \a\t g:i A') . '</p>
                </div>';
    }

    /**
     * Get header image as base64 or return null
     */
    private function getHeaderImage($template)
    {
        if (!$template || empty($template->header_image_path)) {
            return null;
        }

        $imagePath = public_path($template->header_image_path);
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageInfo = getimagesize($imagePath);
            return 'data:' . $imageInfo['mime'] . ';base64,' . $imageData;
        }

        return null;
    }

    /**
     * Get footer image as base64 or return null
     */
    private function getFooterImage($template)
    {
        if (!$template || empty($template->footer_image_path)) {
            return null;
        }

        $imagePath = public_path($template->footer_image_path);
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageInfo = getimagesize($imagePath);
            return 'data:' . $imageInfo['mime'] . ';base64,' . $imageData;
        }

        return null;
    }

    /**
     * Get centre name
     */
    private function getCentreName($centreId)
    {
        try {
            $centre = DB::table('centres')->where('centre_id', $centreId)->first();
            return $centre ? $centre->centre_name : 'CREAMS Centre';
        } catch (Exception $e) {
            return 'CREAMS Centre';
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
            if ($letter->created_by != session('id') && !in_array(session('role'), ['admin', 'supervisor'])) {
                abort(403, 'Unauthorized access to letter');
            }

            $filePath = public_path($letter->letter_file_path);
            
            if (!file_exists($filePath)) {
                throw new Exception('Letter file not found');
            }

            return response()->download($filePath, $letter->letter_reference . '.pdf');

        } catch (Exception $e) {
            Log::error('Letter download error', [
                'letter_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Unable to download letter');
        }
    }

    /**
     * View letter details
     */
    public function show($id)
    {
        try {
            $letter = Letter::with('template')->findOrFail($id);
            
            // Check permissions
            if ($letter->created_by != session('id') && !in_array(session('role'), ['admin', 'supervisor'])) {
                abort(403, 'Unauthorized access to letter');
            }

            return view('letters.show', compact('letter'));

        } catch (Exception $e) {
            Log::error('Letter view error', [
                'letter_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('letters.dashboard')->with('error', 'Unable to load letter');
        }
    }
}