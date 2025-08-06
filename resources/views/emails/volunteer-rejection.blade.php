<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Volunteer Application Update - IIUM PD-CARE</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .status-badge {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #f5c6cb;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
        }
        .feedback-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .encouragement-box {
            background: #d1ecf1;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #bee5eb;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .contact-info {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📧 Application Update</h1>
        <p>IIUM PD-CARE Volunteer Program</p>
    </div>
    
    <div class="content">
        <div class="status-badge">
            Your volunteer application status has been updated
        </div>

        <h2>Dear {{ $volunteer->volunteer_name }},</h2>
        
        <p>Thank you for your interest in volunteering with IIUM PD-CARE and for taking the time to submit your volunteer application. We sincerely appreciate your willingness to support children with special needs in our community.</p>

        <p>After careful consideration of your application, we regret to inform you that we are unable to proceed with your volunteer application at this time.</p>

        @if($volunteer->admin_notes)
        <div class="feedback-box">
            <h3>Feedback from Our Team</h3>
            <p>{{ $volunteer->admin_notes }}</p>
        </div>
        @endif

        <div class="encouragement-box">
            <h3>💙 This Doesn't End Here</h3>
            <p>Please know that this decision does not reflect on your character or your desire to help. There are many factors that influence volunteer placements, including:</p>
            <ul>
                <li>Current volunteer capacity at our centres</li>
                <li>Specific skill requirements for available positions</li>
                <li>Scheduling and availability matches</li>
                <li>Training prerequisites and timing</li>
            </ul>
        </div>

        <h3>Alternative Ways to Support Our Mission</h3>
        <ul>
            <li><strong>Future Applications:</strong> You may reapply after 6 months as our needs change regularly</li>
            <li><strong>Financial Support:</strong> Consider making a donation to support our programs</li>
            <li><strong>Community Awareness:</strong> Help spread awareness about our services in your network</li>
            <li><strong>Skills Development:</strong> Consider developing relevant skills and qualifications for future applications</li>
            <li><strong>Partner Organizations:</strong> We can connect you with other organizations that might benefit from your skills</li>
        </ul>

        <div class="contact-info">
            <h3>Stay Connected</h3>
            <p>We encourage you to stay connected with IIUM PD-CARE:</p>
            <p><strong>Email:</strong> pdcare@iium.edu.my</p>
            <p><strong>Website:</strong> Visit us for updates on volunteer opportunities</p>
            <p><strong>Social Media:</strong> Follow us for community updates and events</p>
        </div>

        <p>Your interest in supporting children with special needs is commendable, and we hope you will continue to find ways to make a positive impact in your community.</p>

        <p style="margin-top: 30px;">Thank you again for considering IIUM PD-CARE as a place to volunteer your time and talents.</p>
        
        <p>With gratitude,<br>
        <strong>The IIUM PD-CARE Volunteer Coordination Team</strong></p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} IIUM PD-CARE - International Islamic University Malaysia</p>
        <p>This email was sent regarding your volunteer application (#VA{{ str_pad($volunteer->id, 6, '0', STR_PAD_LEFT) }})</p>
    </div>
</body>
</html>