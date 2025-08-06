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
        .message-summary {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #c850c0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .contact-info {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .urgent {
            background: #fff5f5;
            border-left-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Message Received</h1>
        <p>Thank you for contacting IIUM PD-CARE</p>
    </div>
    
    <div class="content">
        <h2>Dear {{ $contact->sender_name }},</h2>
        
        <p>Thank you for reaching out to IIUM PD-CARE. We have successfully received your message and wanted to confirm that it has been logged in our system.</p>
        
        <div class="message-summary {{ ($data['urgency'] ?? 'medium') === 'urgent' ? 'urgent' : '' }}">
            <h3>Your Message Summary</h3>
            <p><strong>Subject:</strong> {{ $contact->message_subject ?? 'General Inquiry' }}</p>
            <p><strong>Category:</strong> {{ ucfirst($contact->message_category) }}</p>
            <p><strong>Priority:</strong> {{ ucfirst($data['urgency'] ?? 'medium') }}</p>
            <p><strong>Submitted:</strong> {{ $contact->created_at->format('F j, Y \a\t g:i A') }}</p>
            <p><strong>Reference ID:</strong> {{ $contact->id }}</p>
            
            @if(($data['urgency'] ?? 'medium') === 'urgent')
            <div style="background: #dc3545; color: white; padding: 10px; border-radius: 4px; margin-top: 15px;">
                <strong>🚨 Your message has been marked as urgent and will receive priority attention.</strong>
            </div>
            @endif
        </div>
        
        <div class="contact-info">
            <h3>What Happens Next?</h3>
            <p><strong>Response Timeline:</strong></p>
            <ul>
                @if(($data['urgency'] ?? 'medium') === 'urgent')
                <li>We will respond to your urgent inquiry within <strong>24 hours</strong></li>
                @elseif($data['reason'] === 'complaint')
                <li>Our management team will review your complaint and respond within <strong>48 hours</strong></li>
                @elseif(in_array($data['reason'], ['services', 'admission']))
                <li>Our team will contact you within <strong>2-3 business days</strong></li>
                @elseif($data['reason'] === 'volunteer')
                <li>We will review your volunteer inquiry and contact you within <strong>1 week</strong></li>
                @elseif($data['reason'] === 'partnership')
                <li>Our team will review your proposal and respond within <strong>5 business days</strong></li>
                @else
                <li>We will respond to your inquiry within <strong>3-5 business days</strong></li>
                @endif
                <li>You will receive a response from our team via email or phone</li>
                <li>Please keep your reference ID handy for any follow-up communications</li>
            </ul>
        </div>
        
        <h3>Contact Information</h3>
        <p>If you need immediate assistance or have an urgent matter, please contact us directly:</p>
        <ul>
            <li><strong>Email:</strong> pdcare@iium.edu.my</li>
            <li><strong>Phone:</strong> +60 3-6196 4000</li>
            <li><strong>Address:</strong> IIUM PD-CARE, International Islamic University Malaysia</li>
        </ul>
        
        <p style="margin-top: 30px;">We appreciate your interest in IIUM PD-CARE and look forward to assisting you.</p>
        
        <p>Best regards,<br>
        <strong>The IIUM PD-CARE Team</strong></p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} IIUM PD-CARE - International Islamic University Malaysia</p>
        <p>This is an automated confirmation email. Please do not reply directly to this message.</p>
    </div>
</body>
</html>