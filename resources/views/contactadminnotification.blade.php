<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Message - {{ $contact->formatted_reason }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 700px; margin: 0 auto; padding: 20px; background-color: #f4f4f4; }
        .header { background: linear-gradient(135deg, #32bdea, #c850c0); color: white; text-align: center; padding: 25px; border-radius: 10px 10px 0 0; }
        .content { background: white; padding: 30px; border-radius: 0 0 10px 10px; }
        .details { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .details h3 { margin-top: 0; color: #32bdea; }
        .urgent { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .urgent-high { background: #f8d7da; border: 1px solid #f5c6cb; }
        .button { display: inline-block; background: linear-gradient(135deg, #32bdea, #c850c0); color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .badge-urgent { background: #dc3545; color: white; }
        .badge-high { background: #fd7e14; color: white; }
        .badge-medium { background: #20c997; color: white; }
        .badge-low { background: #6c757d; color: white; }
        .message-preview { background: #f1f3f4; padding: 15px; border-left: 4px solid #32bdea; font-style: italic; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                @if($contact->urgency === 'urgent')
                🚨 URGENT - 
                @elseif($contact->urgency === 'high')
                ⚠️ HIGH PRIORITY - 
                @endif
                New {{ $contact->formatted_reason }}
            </h1>
            <p>Contact form submission received</p>
        </div>
    <div class="content">
        @if($contact->urgency === 'urgent')
        <div class="urgent urgent-high">
            <strong>🚨 URGENT ATTENTION REQUIRED:</strong> This message has been marked as urgent and requires immediate response within 24 hours.
        </div>
        @elseif($contact->urgency === 'high')
        <div class="urgent">
            <strong>⚠️ HIGH PRIORITY:</strong> This message requires priority attention and should be responded to within 48 hours.
        </div>
        @endif
        
        <div class="details">
            <h3>📋 Contact Information</h3>
            <p><strong>Name:</strong> {{ $contact->name }}</p>
            <p><strong>Email:</strong> {{ $contact->email }}</p>
            @if($contact->phone)
            <p><strong>Phone:</strong> {{ $contact->phone }}</p>
            @endif
            @if($contact->organization)
            <p><strong>Organization:</strong> {{ $contact->organization }}</p>
            @endif
            <p><strong>Preferred Contact:</strong> {{ ucfirst($contact->preferred_contact_method ?? 'email') }}</p>
            <p><strong>Reference ID:</strong> #CM{{ str_pad($contact->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p><strong>Submitted:</strong> {{ $contact->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>
        
        <div class="details">
            <h3>📝 Message Details</h3>
            <p><strong>Subject:</strong> {{ $contact->subject }}</p>
            <p><strong>Category:</strong> {{ $contact->formatted_reason }}</p>
            <p>
                <strong>Priority:</strong> 
                <span class="badge badge-{{ $contact->urgency }}">{{ $contact->formatted_urgency }}</span>
                @if($contact->isOverdue())
                <span class="badge badge-urgent">OVERDUE</span>
                @endif
            </p>
        </div>
        
        <div class="details">
            <h3>💬 Message Content</h3>
            <div class="message-preview">
                {{ $contact->message }}
            </div>
        </div>
        
        <div class="details">
            <h3>🔧 Technical Information</h3>
            <p><strong>IP Address:</strong> {{ $contact->ip_address }}</p>
            <p><strong>User Agent:</strong> {{ Str::limit($contact->user_agent, 100) }}</p>
            @if($contact->referrer)
            <p><strong>Referrer:</strong> {{ $contact->referrer }}</p>
            @endif
            <p><strong>Database ID:</strong> {{ $contact->id }}</p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('admin.contacts.show', $contact->id) }}" class="button">
                👁️ View Full Message
            </a>
            <a href="{{ route('admin.contacts.index') }}" class="button">
                📊 Manage All Messages
            </a>
            @if($contact->email)
            <a href="mailto:{{ $contact->email }}?subject=Re: {{ $contact->subject }}&body=Dear {{ $contact->name }},%0D%0A%0D%0AThank you for contacting IIUM PD-CARE regarding your {{ strtolower($contact->formatted_reason) }}.%0D%0A%0D%0A" class="button">
                📧 Reply Directly
            </a>
            @endif
        </div>
        
        <h3>📋 Suggested Actions:</h3>
        <ol>
            @if($contact->reason === 'services')
            <li>Review client's specific rehabilitation needs</li>
            <li>Check current service availability and capacity</li>
            <li>Prepare service information packet</li>
            <li>Schedule initial consultation if appropriate</li>
            <li>Update contact status to "in_progress" when responded</li>
            @elseif($contact->reason === 'volunteer')
            <li>Review volunteer application requirements</li>
            <li>Check current volunteer program openings</li>
            <li>Send volunteer application form if interested</li>
            <li>Schedule informal interview if suitable</li>
            @elseif($contact->reason === 'complaint')
            <li>Assign to appropriate department head immediately</li>
            <li>Investigate the complaint thoroughly</li>
            <li>Document all findings and proposed resolutions</li>
            <li>Respond within 48 hours with action plan</li>
            <li>Schedule follow-up to ensure satisfaction</li>
            @elseif($contact->reason === 'partnership')
            <li>Review partnership proposal and requirements</li>
            <li>Check alignment with organizational goals</li>
            <li>Prepare initial partnership information</li>
            <li>Schedule meeting with relevant stakeholders</li>
            @else
            <li>Review the inquiry and determine appropriate department</li>
            <li>Gather relevant information and resources</li>
            <li>Prepare comprehensive response</li>
            <li>Follow up within the specified timeframe</li>
            @endif
        </ol>
        
        <p><strong>⏰ Response Target:</strong> 
            @if($contact->urgency === 'urgent')
            <span style="color: #dc3545; font-weight: bold;">Within 24 hours</span>
            @elseif($contact->urgency === 'high')
            <span style="color: #fd7e14; font-weight: bold;">Within 48 hours</span>
            @else
            Within 3-5 business days
            @endif
        </p>
        
        @if($contact->reason === 'complaint' || $contact->urgency === 'urgent')
        <div class="urgent urgent-high">
            <strong>⚠️ Important:</strong> This message requires management attention and should be escalated appropriately.
        </div>
        @endif
    </div>
</div>
</body>
</html>