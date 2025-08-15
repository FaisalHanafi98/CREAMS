<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to IIUM PD-CARE Volunteer Team!</title>
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
            background: linear-gradient(135deg, #28a745, #20c997);
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
        .success-badge {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #c3e6cb;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
        }
        .details-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .next-steps {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
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
        <h1>🎉 Congratulations!</h1>
        <p>Welcome to the IIUM PD-CARE Volunteer Team</p>
    </div>
    
    <div class="content">
        <div class="success-badge">
            ✅ Your volunteer application has been APPROVED!
        </div>

        <h2>Dear {{ $volunteer->volunteer_name }},</h2>
        
        <p>We are thrilled to inform you that your volunteer application with IIUM PD-CARE has been approved! Welcome to our dedicated team of volunteers who make a meaningful difference in the lives of children with special needs.</p>

        <div class="details-box">
            <h3>Your Assignment Details</h3>
            <p><strong>Centre Assignment:</strong> {{ $centreName }}</p>
            <p><strong>Location:</strong> {{ $centreAddress }}</p>
            <p><strong>Contact Phone:</strong> <a href="tel:{{ $centrePhone }}">{{ $centrePhone }}</a></p>
            <p><strong>Volunteer ID:</strong> #VA{{ str_pad($volunteer->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p><strong>Approval Date:</strong> {{ $volunteer->status_updated_at->format('F j, Y') }}</p>
            <p><strong>Start Date:</strong> <span style="color: #28a745; font-weight: bold;">{{ $startDate->format('l, F j, Y') }}</span></p>
            <p><strong>Skills/Interests:</strong> {{ $volunteer->volunteer_skills ?: 'General Volunteer Support' }}</p>
            
            @if($volunteer->admin_notes)
            <p><strong>Special Notes:</strong> {{ $volunteer->admin_notes }}</p>
            @endif
        </div>

        <div class="next-steps">
            <h3>📅 Your Volunteer Schedule</h3>
            <p><strong>Schedule Summary:</strong> {{ $scheduleDetails['summary'] }}</p>
            
            @if(!empty($scheduleDetails['formatted_schedule']))
            <div style="background: white; padding: 15px; border-radius: 6px; margin: 15px 0;">
                <h4 style="margin: 0 0 10px 0; color: #28a745;">First Week Schedule ({{ $startDate->format('F j') }} - {{ $startDate->copy()->addDays(6)->format('F j, Y') }}):</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($scheduleDetails['formatted_schedule'] as $schedule)
                    <li style="margin: 5px 0;">
                        <strong>{{ $schedule['day'] }}, {{ $schedule['date'] }}</strong> - {{ $schedule['time'] }}
                    </li>
                    @endforeach
                </ul>
                <div style="margin-top: 15px; padding: 10px; background: #e7f3ff; border-left: 4px solid #007bff; border-radius: 4px;">
                    <p style="margin: 0; font-size: 14px;">
                        <i class="fas fa-map-marker-alt"></i> <strong>Report to:</strong> {{ $centreName }}<br>
                        <i class="fas fa-location-arrow"></i> <strong>Address:</strong> {{ $centreAddress }}<br>
                        <i class="fas fa-phone"></i> <strong>Contact:</strong> {{ $centrePhone }}
                    </p>
                </div>
            </div>
            @endif
        </div>

        <div class="next-steps">
            <h3>🚀 What Happens Next?</h3>
            <ol>
                <li><strong>Orientation Session:</strong> You will be contacted within 2-3 business days to schedule your volunteer orientation before your start date.</li>
                <li><strong>Background Check:</strong> We may require a background verification as per our safety protocols (if applicable).</li>
                <li><strong>Training Program:</strong> Attend mandatory volunteer training sessions specific to your role.</li>
                <li><strong>First Day:</strong> Report to {{ $centreName }} on {{ $startDate->format('l, F j, Y') }} as per your schedule above.</li>
                <li><strong>Start Volunteering:</strong> Begin making a difference in our community!</li>
            </ol>
        </div>

        <div class="contact-info">
            <h3>Important Contacts</h3>
            <p><strong>Volunteer Coordinator:</strong> pdcare@iium.edu.my</p>
            <p><strong>Centre Contact:</strong> {{ $centreName }} - {{ $centrePhone }}</p>
            <p><strong>Centre Address:</strong> {{ $centreAddress }}</p>
            <p><strong>Emergency Contact:</strong> +60 3-6196 4000</p>
        </div>

        <h3>Volunteer Guidelines</h3>
        <ul>
            <li>Always maintain professional conduct and confidentiality</li>
            <li><strong>Arrive punctually</strong> for all scheduled volunteer sessions as per your schedule above</li>
            <li>Follow all centre policies and safety protocols</li>
            <li>Report to the centre reception/front desk on your first day</li>
            <li>Communicate any concerns immediately to your supervisor</li>
            <li>Participate in ongoing training and development programs</li>
        </ul>

        <p>We are excited to have you join our mission of providing comprehensive care and support for children with special needs and their families. Your contribution will make a real difference in our community.</p>

        <p style="margin-top: 30px;">Once again, welcome to the IIUM PD-CARE family!</p>
        
        <p>Best regards,<br>
        <strong>The IIUM PD-CARE Volunteer Team</strong><br>
        {{ $centreName }}</p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} IIUM PD-CARE - International Islamic University Malaysia</p>
        <p>This email was sent to confirm your volunteer approval. Please keep this for your records.</p>
    </div>
</body>
</html>