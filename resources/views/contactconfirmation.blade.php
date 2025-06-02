<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Received - IIUM PD-CARE</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4; }
        .header { background: linear-gradient(135deg, #32bdea, #c850c0); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
        .content { background: white; padding: 30px; border-radius: 0 0 10px 10px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        .details { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .details h3 { margin-top: 0; color: #32bdea; }
        .urgency-badge { display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .urgency-urgent { background: #dc3545; color: white; }
        .urgency-high { background: #fd7e14; color: white; }
        .urgency-medium { background: #20c997; color: white; }
        .urgency-low { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Thank You for Contacting Us!</h1>
            <p>Your message has been received and logged</p>
        </div>
    <div class="content">
        <p>Dear {{ $contact->name }},</p>
        
        <p>Thank you for reaching out to IIUM PD-CARE. We have successfully received your message and want to assure you that it will be reviewed and responded to appropriately.</p>
        
        <div class="details">
            <h3>Message Summary</h3>
            <p><strong>Reference ID:</strong> #CM{{ str_pad($contact->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p><strong>Name:</strong> {{ $contact->name }}</p>
            <p><strong>Email:</strong> {{ $contact->email }}</p>
            @if($contact->phone)
            <p><strong>Phone:</strong> {{ $contact->phone }}</p>
            @endif
            @if($contact->organization)
            <p><strong>Organization:</strong> {{ $contact->organization }}</p>
            @endif
            <p><strong>Subject:</strong> {{ $contact->subject }}</p>
            <p><strong>Reason:</strong> {{ $contact->formatted_reason }}</p>
            <p>
                <strong>Priority:</strong> 
                <span class="urgency-badge urgency-{{ $contact->urgency }}">{{ $contact->formatted_urgency }}</span>
            </p>
            <p><strong>Submitted:</strong> {{ $contact->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>
        
        <div class="details">
            <h3>Your Message</h3>
            <p style="font-style: italic; padding: 15px; background: #f1f3f4; border-left: 4px solid #32bdea;">
                {{ $contact->message }}
            </p>
        </div>
        
        @if($contact->urgency === 'urgent')
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <strong>⚡ Urgent Priority Notice:</strong> Your message has been flagged as urgent and will be reviewed within 24 hours.
        </div>
        @endif
        
        <h3>What happens next?</h3>
        <ol>
            @if($contact->reason === 'services')
            <li><strong>Initial Review:</strong> Our rehabilitation services team will review your inquiry within 2-3 business days.</li>
            <li><strong>Needs Assessment:</strong> We may contact you for additional information about your specific needs.</li>
            <li><strong>Service Recommendation:</strong> We'll provide detailed information about our services that best match your requirements.</li>
            <li><strong>Next Steps:</strong> If appropriate, we'll schedule a consultation or provide enrollment information.</li>
            @elseif($contact->reason === 'volunteer')
            <li><strong>Application Review:</strong> Our volunteer coordinator will review your inquiry within 1 week.</li>
            <li><strong>Initial Contact:</strong> We'll reach out to discuss volunteer opportunities that match your interests.</li>
            <li><strong>Application Process:</strong> If there's a good fit, we'll guide you through our volunteer application process.</li>
            <li><strong>Training:</strong> Successful applicants will participate in our orientation and training program.</li>
            @elseif($contact->reason === 'complaint')
            <li><strong>Immediate Logging:</strong> Your complaint has been logged and assigned to our management team.</li>
            <li><strong>Investigation:</strong> We will investigate the matter thoroughly and impartially.</li>
            <li><strong>Response:</strong> You will receive a detailed response within 48 hours.</li>
            <li><strong>Follow-up:</strong> We may contact you for additional information or to discuss resolution.</li>
            @else
            <li><strong>Review:</strong> Our team will review your message and determine the appropriate response.</li>
            <li><strong>Response:</strong> We will contact you within 3-5 business days with relevant information or next steps.</li>
            <li><strong>Follow-up:</strong> If needed, we may schedule a call or meeting to discuss your inquiry in detail.</li>
            @endif
        </ol>
        
        <h3>Need Immediate Assistance?</h3>
        <p>If your matter is urgent and requires immediate attention, you can contact us directly:</p>
        <ul>
            <li><strong>Phone:</strong> (+60) 3-6421-6421 (Monday - Friday, 9 AM - 5 PM)</li>
            <li><strong>Email:</strong> <a href="mailto:pdcare@iium.edu.my">pdcare@iium.edu.my</a></li>
            <li><strong>Emergency:</strong> For medical emergencies, please call 999 immediately</li>
        </ul>
        
        <p>Please keep your reference number <strong>#CM{{ str_pad($contact->id, 6, '0', STR_PAD_LEFT) }}</strong> for future correspondence regarding this message.</p>
        
        <p>Thank you for your interest in IIUM PD-CARE and for giving us the opportunity to assist you.</p>
        
        <p>Warm regards,<br>
        <strong>IIUM PD-CARE Team</strong><br>
        International Islamic University Malaysia</p>
    </div>
    
    <div class="footer">
        <p>This is an automated confirmation email. Please do not reply to this email address.</p>
        <p>If you need to update your message, please submit a new contact form or call us directly.</p>
        <p>© {{ date('Y') }} IIUM PD-CARE. All rights reserved.</p>
    </div>
</div>
</body>
</html>