<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LetterController extends Controller
{
    /**
     * Display letters archive
     */
    public function index(Request $request)
    {
        try {
            // Get letters based on user role
            $query = Letter::query();
            
            if (session('role') !== 'admin') {
                $query->where('created_by', session('id'));
            }

            // Apply search if provided
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('letter_reference', 'LIKE', "%{$search}%")
                      ->orWhere('letter_subject', 'LIKE', "%{$search}%");
                });
            }

            // Get paginated results
            $letters = $query->orderBy('created_at', 'desc')->paginate(15);

            // Process each letter to ensure data is properly formatted
            foreach ($letters as $letter) {
                // Decode letter_data if it's a JSON string
                if (is_string($letter->letter_data)) {
                    $letter->letter_data = json_decode($letter->letter_data, true) ?: [];
                }
                
                // Ensure letter_data is an array with default values
                if (!is_array($letter->letter_data)) {
                    $letter->letter_data = [];
                }
                
                // Set default values for display
                $letter->letter_data = array_merge([
                    'recipient_name' => 'Unknown',
                    'generated_by_name' => 'Unknown',
                    'recipient_address' => ''
                ], $letter->letter_data);
            }

            return view('letters.index', compact('letters'));

        } catch (\Exception $e) {
            Log::error('Letter index error: ' . $e->getMessage());
            
            // If view doesn't exist, create a basic response
            if (str_contains($e->getMessage(), 'View [letters.index] not found')) {
                return $this->createBasicIndexView($letters ?? collect());
            }
            
            return redirect()->route('dashboard')
                ->with('error', 'Unable to load letters archive: ' . $e->getMessage());
        }
    }

    /**
     * Show letter creation form
     */
    public function create()
    {
        try {
            $template = LetterTemplate::where('is_active', 1)->first();
            return view('letters.create', compact('template'));
        } catch (\Exception $e) {
            // If view doesn't exist, redirect to profile letters tab
            return redirect()->to('/profile#letters-tab')
                ->with('info', 'Please use the letter generation form in your profile.');
        }
    }

    /**
     * Preview letter (called via AJAX)
     */
    public function preview(Request $request)
    {
        try {
            $template = LetterTemplate::where('is_active', 1)->first();
            
            // Create a letter object for preview
            $letter = new \stdClass();
            $letter->letter_reference = 'PREVIEW-' . date('YmdHis');
            $letter->letter_date = $request->letter_date ?: date('Y-m-d');
            $letter->letter_subject = $request->letter_subject ?: 'No Subject';
            $letter->letter_content = $request->letter_content ?: 'No Content';
            $letter->letter_data = [
                'recipient_name' => $request->recipient_name ?: 'Recipient Name',
                'recipient_address' => $request->recipient_address ?: '',
                'generated_by_name' => session('name') ?: 'User',
                'generated_by_position' => session('role') ?: 'Staff'
            ];

            // Check if preview view exists
            if (view()->exists('letters.preview')) {
                $html = view('letters.preview', compact('letter', 'template'))->render();
            } else {
                // Create a basic preview
                $html = $this->createBasicPreview($letter);
            }

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Letter preview error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Preview generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Redirect to download through profile route
     */
    public function download($id)
    {
        // Redirect to the existing profile letter download route
        return redirect()->route('profile.letter.download', $id);
    }

    /**
     * Delete letter
     */
    public function destroy($id)
    {
        try {
            $letter = Letter::findOrFail($id);
            
            // Check permission
            if (session('role') !== 'admin' && $letter->created_by !== session('id')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $letter->delete();

            return response()->json(['success' => true, 'message' => 'Letter deleted successfully']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Delete failed'], 500);
        }
    }

    /**
     * Create basic index view if template is missing
     */
    private function createBasicIndexView($letters)
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Letter Archive</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>
            <div class="container mt-5">
                <h2>Letter Archive</h2>
                <a href="' . route('dashboard') . '" class="btn btn-secondary mb-3">Back to Dashboard</a>
                <a href="/profile#letters-tab" class="btn btn-primary mb-3">Generate New Letter</a>
                
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Subject</th>
                            <th>Recipient</th>
                            <th>Date</th>
                            <th>Generated By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($letters as $letter) {
            $letterData = is_array($letter->letter_data) ? $letter->letter_data : json_decode($letter->letter_data, true);
            $html .= '<tr>
                <td>' . $letter->letter_reference . '</td>
                <td>' . $letter->letter_subject . '</td>
                <td>' . ($letterData['recipient_name'] ?? 'Unknown') . '</td>
                <td>' . $letter->letter_date . '</td>
                <td>' . ($letterData['generated_by_name'] ?? 'Unknown') . '</td>
                <td>';
            
            if ($letter->letter_file_path) {
                $html .= '<a href="' . route('profile.letter.download', $letter->id) . '" class="btn btn-sm btn-primary">Download</a>';
            }
            
            $html .= '</td></tr>';
        }

        $html .= '</tbody></table>';
        
        if ($letters->hasPages()) {
            $html .= $letters->links();
        }
        
        $html .= '</div></body></html>';

        return response($html);
    }

    /**
     * Create basic preview HTML
     */
    private function createBasicPreview($letter)
    {
        return '
        <div style="padding: 20px; font-family: Arial, sans-serif;">
            <h4>Letter Preview</h4>
            <hr>
            <p><strong>Date:</strong> ' . $letter->letter_date . '</p>
            <p><strong>Reference:</strong> ' . $letter->letter_reference . '</p>
            <p><strong>To:</strong> ' . $letter->letter_data['recipient_name'] . '</p>
            <p><strong>Subject:</strong> ' . $letter->letter_subject . '</p>
            <hr>
            <div style="white-space: pre-wrap;">' . htmlspecialchars($letter->letter_content) . '</div>
            <br><br>
            <p>
                <strong>' . $letter->letter_data['generated_by_name'] . '</strong><br>
                ' . $letter->letter_data['generated_by_position'] . '<br>
                CREAMS System
            </p>
        </div>';
    }
}