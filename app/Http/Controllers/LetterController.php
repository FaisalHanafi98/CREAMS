<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Exception;

class LetterController extends Controller
{
    /**
     * Generate and download letter
     */
    public function generate(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'letter_ref' => 'required|string|max:100',
                'letter_date' => 'required|date',
                'recipient_name' => 'required|string|max:255',
                'recipient_address' => 'required|string|max:500',
                'letter_subject' => 'required|string|max:255',
                'letter_content' => 'required|string|max:5000',
                'header_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'footer_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Handle image uploads
            $headerImagePath = null;
            $footerImagePath = null;

            if ($request->hasFile('header_image')) {
                $headerImagePath = $request->file('header_image')->store('letters/headers', 'public');
            }

            if ($request->hasFile('footer_image')) {
                $footerImagePath = $request->file('footer_image')->store('letters/footers', 'public');
            }

            // Generate HTML content
            $html = $this->generateLetterHTML($validated, $headerImagePath, $footerImagePath);

            // For now, return the HTML as downloadable file
            // In a real implementation, you'd use a PDF library like DomPDF or wkhtmltopdf
            $filename = 'letter_' . str_replace(['/', '\\', ' '], '_', $validated['letter_ref']) . '.html';
            
            return Response::make($html, 200, [
                'Content-Type' => 'text/html',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate letter: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function generateLetterHTML($data, $headerImagePath = null, $footerImagePath = null)
    {
        $user = session('name', 'Administrator');
        $role = session('role', 'admin');
        
        $roleDisplayNames = [
            'admin' => 'System Administrator',
            'supervisor' => 'Centre Supervisor', 
            'teacher' => 'Special Education Teacher',
            'ajk' => 'Activity Coordinator'
        ];
        $roleDisplay = $roleDisplayNames[$role] ?? ucfirst($role);

        $headerImage = '';
        if ($headerImagePath) {
            $headerImage = '<img src="' . asset('storage/' . $headerImagePath) . '" style="max-width: 100%; height: auto; margin-bottom: 20px;">';
        }

        $footerImage = '';
        if ($footerImagePath) {
            $footerImage = '<img src="' . asset('storage/' . $footerImagePath) . '" style="max-width: 100%; height: auto; margin-top: 20px;">';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Letter - {$data['letter_ref']}</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .letter-info {
            text-align: right;
            margin-bottom: 30px;
        }
        .recipient {
            margin-bottom: 30px;
        }
        .subject {
            margin-bottom: 30px;
            font-weight: bold;
        }
        .content {
            margin-bottom: 40px;
            text-align: justify;
        }
        .signature {
            margin-top: 50px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        {$headerImage}
    </div>
    
    <div class="letter-info">
        <strong>Ref: {$data['letter_ref']}</strong><br>
        <strong>Date: {$data['letter_date']}</strong>
    </div>
    
    <div class="recipient">
        <strong>To:</strong><br>
        {$data['recipient_name']}<br>
        {$data['recipient_address']}
    </div>
    
    <div class="subject">
        <strong>Subject: {$data['letter_subject']}</strong>
    </div>
    
    <div class="content">
        {$data['letter_content']}
    </div>
    
    <div class="signature">
        <p>Yours sincerely,</p>
        <br><br>
        <p>
            <strong>{$user}</strong><br>
            {$roleDisplay}<br>
            IIUM PD-CARE
        </p>
    </div>
    
    <div class="footer">
        {$footerImage}
    </div>
</body>
</html>
HTML;
    }

    /**
     * Delete a letter
     */
    public function destroy($id)
    {
        try {
            $letter = \App\Models\Letter::findOrFail($id);
            
            // Check if user has permission to delete this letter
            if (session('role') !== 'admin' && $letter->created_by !== session('id')) {
                return redirect()->back()->with('error', 'Access denied to delete this letter.');
            }
            
            // Delete the PDF file if it exists
            if ($letter->letter_file_path) {
                $pdfPath = public_path('letters/' . basename($letter->letter_file_path));
                if (file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
            }
            
            // Delete the letter record
            $letter->delete();
            
            return redirect()->back()->with('success', 'Letter deleted successfully.');
            
        } catch (\Exception $e) {
            \Log::error('Letter deletion error', [
                'message' => $e->getMessage(),
                'letter_id' => $id,
                'user_id' => session('id')
            ]);
            
            return redirect()->back()->with('error', 'Failed to delete letter: ' . $e->getMessage());
        }
    }
}