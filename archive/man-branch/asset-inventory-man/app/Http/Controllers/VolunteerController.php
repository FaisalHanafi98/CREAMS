<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Volunteers;
use Illuminate\Support\Facades\Mail;

class VolunteerController extends Controller
{
    public function index()
    {
        return view('volunteer');
    }

    public function submit(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'interest' => 'required|string',
            'message' => 'required|string',
            'availability' => 'required|string'
        ]);

        // Save application to database
        $application = Volunteers::create($validatedData);

        // Send confirmation email
        Mail::send('emails.volunteer-application', ['application' => $application], function ($message) use ($application) {
            $message->to($application->email)
                    ->subject('Volunteer Application Received - CREAMS');
        });

        return redirect()->back()->with('success', 'Thank you for your volunteer application! We will review it and contact you soon.');
    }
}