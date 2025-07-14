<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Application Confirmation - IIUM PD-CARE</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4; }
        .header { background: linear-gradient(135deg, #32bdea, #c850c0); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
        .content { background: white; padding: 30px; border-radius: 0 0 10px 10px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        .button { display: inline-block; background: linear-gradient(135deg, #32bdea, #c850c0); color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .details { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .details h3 { margin-top: 0; color: #32bdea; }
        .timeline { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .timeline-step { margin-bottom: 15px; padding-left: 25px; position: relative; }
        .timeline-step::before { content: "✓"; position: absolute; left: 0; color: #32bdea; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤝 Thank You for Your Application!</h1>
            <p>Your volunteer application has been received</p>
        </div>
    <div class="content">
        <p>Dear {{ $application->first_name }},</p>
        
        <p>Thank you for your interest in volunteering with IIUM PD-CARE! We have successfully received your volunteer application and are excited about your willingness to support children with special needs in our community.</p>
        
        <div class="details">
            <h3>📋 Application Summary</h3>
            <p><strong>Name:</strong> {{ $application->name }}</p>
            <p><strong>Email:</strong> {{ $application->email }}</p>
            <p><strong>Phone:</strong> {{ $application->phone }}</p>
            @if($application->address)
            <p><strong>Address:</strong> {{ $application->address }}</p>
            @endif
            @if($application->city)
            <p><strong>City:</strong> {{ $application->city }}, {{ $application->postcode }}</p>
            @endif
            <p><strong>Area of Interest:</strong> {{ $application->formatted_interest }}</p>
            <p><strong>Time Commitment:</strong> {{ $application->formatted_commitment }}</p>
            <p><strong>Availability:</strong> {{ $application->formatted_availability }}</p>
            <p><strong>Application ID:</strong> #VA{{ str_pad($application->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p><strong>Submitted:</strong> {{ $application->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>
        
        <div class="details">
            <h3>💭 Your Motivation</h3>
            <p style="font-style: italic; padding: 15px; background: #f1f3f4; border-left: 4px solid #32bdea;">
                "{{ $application->motivation }}"
            </p>
        </div>
        
        @if($application->skills)
        <div class="details">
            <h3>🎯 Your Skills & Experience</h3>
            <p>{{ $application->skills }}</p>
        </div>
        @endif
        
        @if($application->experience)
        <div class="details">
            <h3>📚 Previous Experience</h3>
            <p>{{ $application->experience }}</p>
        </div>
        @endif
        
        <div class="timeline">
            <h3>🗺️ What happens next?</h3>
            <div class="timeline-step">
                <strong>Application Review (Week 1):</strong> Our volunteer coordinator will review your application and check it against our current volunteer needs and opportunities.
            </div>
            <div class="timeline-step">
                <strong>Initial Contact (Week 1-2):</strong> If your application matches our current requirements, we'll contact you via phone or email for a brief initial conversation.
            </div>
            <div class="timeline-step">
                <strong>Interview Process (Week 2-3):</strong> Suitable candidates will be invited for a more detailed interview (in-person or virtual) to discuss your interests, availability, and how you can best contribute.
            </div>
            <div class="timeline-step">
                <strong>Background Check (Week 3-4):</strong> As part of our child protection policy, successful candidates will undergo a background screening process.
            </div>
            <div class="timeline-step">
                <strong>Orientation & Training (Week 4-5):</strong> You'll participate in our comprehensive volunteer orientation program covering child protection, disability awareness, and role-specific training.
            </div>
            <div class="timeline-step">
                <strong>Volunteer Placement (Week 5-6):</strong> We'll match you with a volunteer opportunity that aligns with your skills, interests, and availability.
            </div>
        </div>
        
        <div class="details">
            <h3>📞 Contact Information</h3>
            <p>If you have any questions about your application or our volunteer program, please don't hesitate to reach out:</p>
            <ul>
                <li><strong>Volunteer Coordinator:</strong> <a href="mailto:pdcare@iium.edu.my">pdcare@iium.edu.my</a></li>
                <li><strong>Phone:</strong> (+60) 3-6421-6421 (Monday - Friday, 9 AM - 5 PM)</li>
                <li><strong>Office:</strong> IIUM Gombak Campus, Selangor, Malaysia</li>
            </ul>
        </div>
        
        <div class="details">
            <h3>🔗 Useful Resources</h3>
            <p>While you wait, feel free to:</p>
            <ul>
                <li>Visit our website to learn more about our programs and impact</li>
                <li>Follow us on social media for updates and volunteer stories</li>
                <li>Read about our approach to community-based rehabilitation</li>
                <li>Connect with us on LinkedIn for professional networking</li>
            </ul>
        </div>
        
        <p><strong>Please keep your application reference number #VA{{ str_pad($application->id, 6, '0', STR_PAD_LEFT) }} for future correspondence.</strong></p>
        
        <p>Thank you again for your commitment to making a difference in the lives of children with special needs. We look forward to the possibility of welcoming you to our volunteer team!</p>
        
        <p>Warm regards,<br>
        <strong>IIUM PD-CARE Volunteer Coordination Team</strong><br>
        International Islamic University Malaysia<br>
        Community-based Rehabilitation Center</p>
    </div>
    
    <div class="footer">
        <p>This is an automated confirmation email. Please do not reply to this email address.</p>
        <p>If you need to update your application, please contact us directly at pdcare@iium.edu.my</p>
        <p>© {{ date('Y') }} IIUM PD-CARE. All rights reserved.</p>
    </div>
</div>
</body>
</html>