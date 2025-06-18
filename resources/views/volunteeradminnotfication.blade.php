!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Volunteer Application Received</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4; }
        .header { background: linear-gradient(135deg, #32bdea, #c850c0); color: white; text-align: center; padding: 20px; border-radius: 10px 10px 0 0; }
        .content { background: white; padding: 30px; border-radius: 0 0 10px 10px; }
        .details { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .details h3 { margin-top: 0; color: #32bdea; }
        .urgent { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .button { display: inline-block; background: linear-gradient(135deg, #32bdea, #c850c0); color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🆕 New Volunteer Application</h1>
            <p>A new volunteer application has been submitted</p>
        </div>
    <div class="content">
        <div class="urgent">
            <strong>⚡ Action Required:</strong> A new volunteer application requires review and follow-up.
        </div>
        
        <div class="details">
            <h3>Applicant Information</h3>
            <p><strong>Name:</strong> {{ $application->name }}</p>
            <p><strong>Email:</strong> {{ $application->email }}</p>
            <p><strong>Phone:</strong> {{ $application->phone }}</p>
            <p><strong>Location:</strong> {{ $application->city ? $application->city . ', ' : '' }}{{ $application->postcode }}</p>
            <p><strong>Application ID:</strong> #VA{{ str_pad($application->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p><strong>Submitted:</strong> {{ $application->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>
        
        <div class="details">
            <h3>Volunteer Preferences</h3>
            <p><strong>Area of Interest:</strong> {{ $application->formatted_interest }}</p>
            <p><strong>Time Commitment:</strong> {{ $application->formatted_commitment }}</p>
            <p><strong>Availability:</strong> {{ $application->formatted_availability }}</p>
            @if($application->skills)
            <p><strong>Skills & Experience:</strong> {{ Str::limit($application->skills, 200) }}</p>
            @endif
        </div>
        
        <div class="details">
            <h3>Motivation</h3>
            <p>{{ Str::limit($application->motivation, 300) }}</p>
            @if(strlen($application->motivation) > 300)
            <p><em>... (view full application for complete message)</em></p>
            @endif
        </div>
        
        @if($application->experience)
        <div class="details">
            <h3>Previous Experience</h3>
            <p>{{ Str::limit($application->experience, 200) }}</p>
        </div>
        @endif
        
        <div class="details">
            <h3>Technical Information</h3>
            <p><strong>IP Address:</strong> {{ $application->ip_address }}</p>
            <p><strong>Referral Source:</strong> {{ $application->referral ? ucfirst(str_replace('-', ' ', $application->referral)) : 'Not specified' }}</p>
            <p><strong>Database ID:</strong> {{ $application->id }}</p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('admin.volunteers.show', $application->id) }}" class="button">
                📋 View Full Application
            </a>
            <a href="{{ route('admin.volunteers.index') }}" class="button">
                📊 Manage All Applications
            </a>
        </div>
        
        <h3>Next Steps:</h3>
        <ol>
            <li>Review the complete application in the admin panel</li>
            <li>Conduct initial screening call within 7 days</li>
            <li>Update application status in the system</li>
            <li>Schedule interview if candidate meets requirements</li>
            <li>Send response email to applicant</li>
        </ol>
        
        <p><strong>⏰ Response Target:</strong> Please respond to this application within 7-10 business days to maintain our professional standards.</p>
    </div>
</div>
</body>
</html>