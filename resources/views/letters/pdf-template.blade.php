<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $letter->subject }}</title>
    <style>
        @page {
            margin: 2cm 1.5cm;
            font-family: 'Times New Roman', serif;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .header img {
            max-width: 100%;
            max-height: 120px;
            margin-bottom: 10px;
        }
        
        .header-content {
            font-size: 10pt;
            margin-top: 10px;
            color: #333;
        }
        
        .letter-info {
            margin-bottom: 30px;
        }
        
        .reference {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 10pt;
            color: #666;
            font-weight: bold;
        }
        
        .letter-date {
            text-align: right;
            margin-bottom: 25px;
            font-size: 11pt;
        }
        
        .recipient {
            margin-bottom: 25px;
            line-height: 1.4;
        }
        
        .recipient-name {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 5px;
        }
        
        .recipient-address {
            font-size: 11pt;
            color: #333;
        }
        
        .subject {
            font-weight: bold;
            margin: 25px 0;
            font-size: 12pt;
            text-decoration: underline;
        }
        
        .content {
            text-align: justify;
            margin: 30px 0;
            white-space: pre-wrap;
            line-height: 1.6;
            font-size: 12pt;
        }
        
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        
        .signature-closing {
            margin-bottom: 15px;
        }
        
        .signature-space {
            margin: 40px 0 10px 0;
        }
        
        .signature-name {
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
            display: inline-block;
            min-width: 200px;
        }
        
        .signature-position {
            font-size: 11pt;
            margin-top: 5px;
        }
        
        .signature-organization {
            font-size: 11pt;
            margin-top: 2px;
            font-style: italic;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            padding: 15px 0;
            border-top: 1px solid #ccc;
            font-size: 9pt;
            color: #666;
        }
        
        .footer img {
            max-width: 100%;
            max-height: 60px;
            margin-bottom: 5px;
        }
        
        .footer-content {
            margin-top: 5px;
        }
        
        /* Ensure proper page breaks */
        .page-break {
            page-break-before: always;
        }
        
        /* Print-specific styles */
        @media print {
            .header {
                position: running(header);
            }
            
            .footer {
                position: running(footer);
            }
        }
        
        /* Letterhead styling */
        .letterhead {
            margin-bottom: 20px;
        }
        
        /* Content paragraphs */
        .content p {
            margin-bottom: 12pt;
        }
        
        .content p:first-child {
            margin-top: 0;
        }
        
        .content p:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <!-- Reference Number -->
    <div class="reference">
        Ref: {{ $letter->reference_number }}
    </div>
    
    <!-- Header Section -->
    <div class="letterhead">
        @if($template && $template->header_image)
            <div class="header">
                <img src="{{ public_path('storage/' . $template->header_image) }}" alt="Header">
            </div>
        @endif
        
        @if($template && $template->header_content)
            <div class="header-content">
                {!! nl2br(e($template->header_content)) !!}
            </div>
        @endif
    </div>
    
    <!-- Letter Date -->
    <div class="letter-date">
        {{ $letter->letter_date->format('d F Y') }}
    </div>
    
    <!-- Recipient Information -->
    <div class="recipient">
        <div class="recipient-name">{{ $letter->recipient_name }}</div>
        @if($letter->recipient_address)
            <div class="recipient-address">
                {!! nl2br(e($letter->recipient_address)) !!}
            </div>
        @endif
    </div>
    
    <!-- Subject Line -->
    <div class="subject">
        Subject: {{ $letter->subject }}
    </div>
    
    <!-- Greeting -->
    <div class="greeting">
        Dear {{ $letter->recipient_name }},
    </div>
    
    <!-- Letter Content -->
    <div class="content">
        {!! nl2br(e($letter->content)) !!}
    </div>
    
    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-closing">
            Yours sincerely,
        </div>
        
        <div class="signature-space">
            <!-- Space for handwritten signature -->
        </div>
        
        <div class="signature-details">
            <div class="signature-name">{{ $letter->generated_by_name }}</div>
            <div class="signature-position">{{ $letter->generated_by_position }}</div>
            <div class="signature-organization">{{ config('app.name', 'CREAMS') }}</div>
        </div>
    </div>
    
    <!-- Footer Section -->
    @if($template && ($template->footer_image || $template->footer_content))
        <div class="footer">
            @if($template->footer_image)
                <img src="{{ public_path('storage/' . $template->footer_image) }}" alt="Footer">
            @endif
            
            @if($template->footer_content)
                <div class="footer-content">
                    {!! nl2br(e($template->footer_content)) !!}
                </div>
            @endif
        </div>
    @endif
</body>
</html>