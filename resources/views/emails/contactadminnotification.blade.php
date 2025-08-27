<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Contact Message - IIUM PD-CARE</title>
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
            background: linear-gradient(135deg, #c850c0, #32bdea);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .message-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #c850c0;
        }
        .urgent {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        .high {
            border-left-color: #ffc107;
            background: #fffbf0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .action-button {
            display: inline-block;
            background: #c850c0;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 0;
        }
        .contact-info {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🆕 New Contact Message</h1>
        <p>IIUM PD-CARE Admin Notification</p>
    </div>
    
    <div class="content">
        <h2>New Contact Form Submission</h2>
        
        <p>A new message has been submitted through the IIUM PD-CARE contact form.</p>
        
        <div class="message-details {{ ($data['urgency'] ?? 'medium') === 'urgent' ? 'urgent' : (($data['urgency'] ?? 'medium') === 'high' ? 'high' : '') }}">
            <h3>Contact Information</h3>
            <p><strong>Name:</strong> {{ $contact->name }}</p>
            <p><strong>Email:</strong> {{ $contact->email }}</p>
            @if($contact->phone)
            <p><strong>Phone:</strong> {{ $contact->phone }}</p>
            @endif
            
            <h3>Message Details</h3>
            <p><strong>Subject:</strong> {{ $contact->subject ?? 'General Inquiry' }}</p>
            <p><strong>Category:</strong> {{ ucfirst($data['reason'] ?? 'General') }}</p>
            <p><strong>Priority:</strong> {{ ucfirst($data['urgency'] ?? 'medium') }}</p>
            <p><strong>Submitted:</strong> {{ $contact->created_at->format('F j, Y \a\t g:i A') }}</p>
            
            <h3>Message Content</h3>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; border-left: 3px solid #c850c0;">
                {!! nl2br(e($contact->message)) !!}
            </div>
        </div>
        
        @if(($data['urgency'] ?? 'medium') === 'urgent')
        <div style="background: #dc3545; color: white; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <h3 style="margin: 0; color: white;">🚨 URGENT MESSAGE</h3>
            <p style="margin: 5px 0 0 0;">This message has been marked as urgent and requires immediate attention.</p>
        </div>
        @endif
        
        <div class="contact-info">
            <h3>Response Guidelines</h3>
            <ul>
                <li><strong>Urgent:</strong> Respond within 24 hours</li>
                <li><strong>High Priority:</strong> Respond within 48 hours</li>
                <li><strong>Medium/Low:</strong> Respond within 3-5 business days</li>
            </ul>
        </div>
        
        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Review the message content and priority level</li>
            <li>Assign to appropriate team member if needed</li>
            <li>Respond to the sender via email or phone</li>
            <li>Update the message status in the admin panel</li>
        </ol>
        
        <p style="margin-top: 30px;">
            <a href="mailto:{{ $contact->email }}" class="action-button">Reply via Email</a>
        </p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} IIUM PD-CARE Admin System</p>
        <p>This is an automated notification. Contact ID: {{ $contact->id }}</p>
    </div>
</body>
</html>