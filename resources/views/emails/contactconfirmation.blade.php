<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Message Received - IIUM PD-CARE</title>
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
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .contact-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>IIUM PD-CARE</h1>
        <p>Community-based Rehabilitation Center</p>
    </div>
    
    <div class="content">
        <h2>Thank you for contacting us!</h2>
        
        <p>Dear {{ $contact->sender_name }},</p>
        
        <p>We have successfully received your message and appreciate you reaching out to IIUM PD-CARE. Our team will review your inquiry and respond as soon as possible.</p>
        
        <div class="contact-details">
            <h3>Your Message Details:</h3>
            <p><strong>Subject:</strong> {{ $contact->message_subject ?? 'General Inquiry' }}</p>
            <p><strong>Category:</strong> {{ ucfirst($contact->message_category) }}</p>
            <p><strong>Submitted:</strong> {{ $contact->created_at->format('F j, Y \a\t g:i A') }}</p>
            @if($contact->urgency)
            <p><strong>Priority:</strong> {{ ucfirst($contact->urgency) }}</p>
            @endif
        </div>
        
        <p><strong>What happens next?</strong></p>
        <ul>
            <li>Our team will review your message within 24-48 hours</li>
            <li>We will respond via email or phone based on your preference</li>
            <li>For urgent matters, please call us directly at (+60) 3-6421-6421</li>
        </ul>
        
        <p>If you have any immediate questions or concerns, please don't hesitate to contact us directly:</p>
        <ul>
            <li><strong>Email:</strong> pdcare@iium.edu.my</li>
            <li><strong>Phone:</strong> (+60) 3-6421-6421</li>
            <li><strong>Address:</strong> IIUM Gombak Campus, Selangor, Malaysia</li>
        </ul>
        
        <p>Thank you for your interest in IIUM PD-CARE.</p>
        
        <p>Best regards,<br>
        <strong>IIUM PD-CARE Team</strong><br>
        Community-based Rehabilitation Center</p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} IIUM PD-CARE. All rights reserved.</p>
        <p>This is an automated message. Please do not reply directly to this email.</p>
    </div>
</body>
</html>