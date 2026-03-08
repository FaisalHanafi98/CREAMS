<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $letter->letter_subject }}</title>
    <style>
        @page {
            margin: 2cm 2cm 2cm 2cm;
            font-family: 'Times New Roman', serif;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        * {
            box-sizing: border-box;
        }
        
        div, p, br {
            margin-left: 0 !important;
            padding-left: 0 !important;
            text-indent: 0 !important;
        }
        
        .letterhead {
            text-align: center;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .letterhead .centre-name {
            font-size: 24pt;
            font-weight: bold;
            color: #1e40af;
            margin: 0 0 10px 0;
        }
        
        .letterhead .centre-address {
            font-size: 11pt;
            color: #666;
            margin: 0;
        }
        
        .letter-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }
        
        .letter-reference {
            font-weight: bold;
            font-size: 11pt;
        }
        
        .letter-date {
            text-align: right;
            font-size: 11pt;
        }
        
        .recipient-section {
            margin-bottom: 30px;
        }
        
        .recipient-address {
            font-size: 11pt;
            line-height: 1.4;
        }
        
        .letter-subject {
            font-weight: bold;
            margin-bottom: 25px;
            text-decoration: underline;
            font-size: 12pt;
        }
        
        .letter-content {
            text-align: justify;
            margin-bottom: 40px;
            font-size: 12pt;
            line-height: 1.8;
            white-space: pre-wrap;
            margin-left: 0 !important;
            padding-left: 0 !important;
            margin-right: 0;
            padding-right: 0;
            width: 100%;
            display: block;
        }
        
        .letter-content p {
            margin-bottom: 15px;
            margin-left: 0;
            padding-left: 0;
            text-indent: 0;
        }
        
        .letter-content div {
            margin-left: 0;
            padding-left: 0;
        }
        
        .letter-signature {
            margin-top: 50px;
        }
        
        .signature-line {
            margin-top: 40px;
            margin-bottom: 5px;
        }
        
        .sender-info {
            font-weight: bold;
        }
        
        .sender-position {
            font-style: italic;
            margin-top: 5px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt;
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
            font-weight: bold;
        }
        
        .footer {
            position: fixed;
            bottom: 1cm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        
        .urgent-marking {
            background-color: #fee;
            border: 2px solid #f00;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            color: #c00;
        }
        
        .confidential-marking {
            background-color: #f0f0f0;
            border: 2px solid #666;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <!-- Watermark for draft letters -->
    @if($letter->letter_status === 'draft')
        <div class="watermark">DRAFT</div>
    @endif
    
    <!-- Header and Letterhead -->
    <div class="letterhead">
        <div class="centre-name">{{ session('centre_name', 'CREAMS Centre') }}</div>
        <div class="centre-address">
            {{ session('centre_address', 'Centre Address') }}<br>
            Tel: {{ session('centre_phone', '+60-XXX-XXXXXXX') }} | 
            Email: {{ session('centre_email', 'info@iium.edu.my') }}
        </div>
    </div>
    
    <!-- Letter Reference and Date -->
    <div class="letter-header">
        <div class="letter-reference">
            <strong>Ref: {{ $letter->letter_id }}</strong>
        </div>
        <div class="letter-date">
            {{ $letter->created_at->format('F j, Y') }}
        </div>
    </div>
    
    <!-- Priority Markings -->
    @if(isset($letterData['priority']) && $letterData['priority'] === 'urgent')
        <div class="urgent-marking">
            *** URGENT ***
        </div>
    @elseif(isset($letterData['priority']) && $letterData['priority'] === 'high')
        <div class="urgent-marking" style="background-color: #fff3cd; border-color: #856404; color: #856404;">
            *** HIGH PRIORITY ***
        </div>
    @endif
    
    @if(isset($letterData['confidential']) && $letterData['confidential'])
        <div class="confidential-marking">
            CONFIDENTIAL
        </div>
    @endif
    
    <!-- Recipient Information -->
    <div class="recipient-section">
        <div class="recipient-address">
            <strong>{{ $letterData['recipient_name'] ?? 'Recipient Name' }}</strong><br>
            {!! nl2br($letterData['recipient_address'] ?? 'Recipient Address') !!}
        </div>
    </div>
    
    <!-- Letter Subject -->
    <div class="letter-subject">
        <strong>Subject: {{ $letter->letter_subject }}</strong>
    </div>
    
    <!-- Letter Content -->
    <div class="letter-content">
        {!! nl2br($content) !!}
    </div>
    
    <!-- Letter Signature -->
    <div class="letter-signature">
        <p>Sincerely,</p>
        
        <div class="signature-line">
            _________________________________
        </div>
        
        <div class="sender-info">
            <strong>{{ $letter->creator->name ?? 'Staff Member' }}</strong>
        </div>
        
        <div class="sender-position">
            {{ $letter->creator->position ?? 'Staff' }}<br>
            {{ session('centre_name', 'CREAMS Centre') }}
        </div>
        
        @if(isset($letterData['sender_contact']) && !empty($letterData['sender_contact']))
        <div style="margin-top: 10px; font-size: 10pt;">
            Contact: {{ $letterData['sender_contact'] }}
        </div>
        @endif
    </div>
    
    <!-- Copy Information -->
    @if(isset($letterData['send_copy_to']) && !empty($letterData['send_copy_to']))
    <div style="margin-top: 30px; font-size: 10pt;">
        <strong>cc:</strong> {{ $letterData['send_copy_to'] }}
    </div>
    @endif
    
    <!-- Attachments -->
    @if(isset($letterData['attachments']) && !empty($letterData['attachments']))
    <div style="margin-top: 20px; font-size: 10pt;">
        <strong>Attachments:</strong>
        <ul style="margin-left: 20px;">
            @foreach($letterData['attachments'] as $attachment)
                <li>{{ $attachment }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        {{ session('centre_name', 'CREAMS Centre') }} | 
        Generated on {{ now()->format('F j, Y \a\t g:i A') }} | 
        Reference: {{ $letter->letter_id }}
        
        @if(isset($letterData['page_numbering']) && $letterData['page_numbering'])
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
                    $size = 10;
                    $font = $fontMetrics->getFont("Helvetica");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = $pdf->get_height() - 35;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        @endif
    </div>
    
    <!-- Additional Pages for Long Content -->
    @if(strlen($content) > 2000)
        <!-- This would handle multi-page content -->
    @endif
</body>
</html>