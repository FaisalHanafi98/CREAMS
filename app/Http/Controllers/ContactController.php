<?php

namespace App\Http\Controllers;

use App\Models\ContactMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function index()
    {
        return view('contactus');
    }

    public function submit(Request $request)
    {
        try {
            // Validate form data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'reason' => 'required|in:services,support,volunteer,partnership,general,other',
                'message' => 'required|string'
            ]);

            // Save to database
            $contact = ContactMessages::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'] ?? null,
                'reason' => $validatedData['reason'],
                'message' => $validatedData['message'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Detailed Email Sending with Extensive Logging
            try {
                info('Attempting to send email', [
                    'to' => 'asbourne1998@gmail.com',
                    'data' => $validatedData
                ]);

                Mail::send('emails.contact-message', ['data' => $validatedData], function ($message) use ($validatedData) {
                    $message->to('asbourne1998@gmail.com')
                            ->from(config('mail.from.address'), config('mail.from.name'))
                            ->subject('New CREAMS Contact Form - ' . ucfirst($validatedData['reason']));
                });

                info('Email sent successfully');
            } catch (\Exception $e) {
                // Log detailed error information
                logger()->error('Email sending failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Redirect back with success message
            return redirect()->back()->with('success', 'Thank you for your message! We will get back to you soon.');

        } catch (\Exception $e) {
            // Use global logging helper
            logger()->error('Contact form submission error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return with an error message
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred. Please try again.']);
        }
    }
}