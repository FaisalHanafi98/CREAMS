<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $letter->letter_reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            background: white;
        }
        
        .letter-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
            position: relative;
        }
        
        /* Header Styles */
        .letter-header {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 3px solid #32bdea;
            padding-bottom: 20px;
        }
        
        .header-image {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .header-image img {
            max-width: 200px;
            max-height: 80px;
            height: auto;
        }
        
        .header-content {
            text-align: center;
        }
        
        .header-title {
            color: #32bdea;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .header-subtitle {
            color: #666;
            font-size: 14px;
            font-style: italic;
            margin-bottom: 10px;
        }
        
        .header-info {
            font-size: 11px;
            color: #888;
        }
        
        /* Letter Content */
        .letter-content {
            margin-bottom: 50px;
            min-height: 400px;
        }
        
        .letter-reference {
            text-align: right;
            margin-bottom: 25px;
            font-weight: bold;
            color: #32bdea;
            font-size: 14px;
        }
        
        .letter-date {
            text-align: right;
            margin-bottom: 25px;
            font-size: 12px;
            color: #666;
        }
        
        .recipient-info {
            margin-bottom: 25px;
        }
        
        .recipient-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .recipient-details {
            margin-left: 20px;
            margin-bottom: 10px;
        }
        
        .letter-subject {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 25px 0;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .letter-body {
            text-align: justify;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .letter-body p {
            margin-bottom: 15px;
        }
        
        .signature-section {
            margin-top: 40px;
            text-align: right;
        }
        
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #333;
            width: 200px;
            margin-left: auto;
            text-align: center;
            padding-top: 5px;
        }
        
        .signature-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .signature-title {
            font-size: 11px;
            color: #666;
        }
        
        /* Footer Styles */
        .letter-footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            border-top: 2px solid #e0e0e0;
            padding-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .footer-image {
            margin-bottom: 10px;
        }
        
        .footer-image img {
            max-width: 150px;
            max-height: 50px;
            height: auto;
        }
        
        .footer-content {
            line-height: 1.4;
        }
        
        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .mb-10 { margin-bottom: 10px; }
        .mb-20 { margin-bottom: 20px; }
        .mt-20 { margin-top: 20px; }
        
        /* Page Break */
        .page-break {
            page-break-after: always;
        }
        
        @page {
            margin: 20mm;
            @bottom-center {
                content: counter(page) " of " counter(pages);
                font-size: 10px;
                color: #666;
            }
        }
    </style>
</head>
<body>
    <div class="letter-container">
        <!-- Header Section -->
        <div class="letter-header">
            @if($header_image)
                <div class="header-image">
                    <img src="{{ $header_image }}" alt="Header Image">
                </div>
            @endif
            
            <div class="header-content">
                @if($template && $template->header_content)
                    {!! $header_content !!}
                @else
                    <div class="header-title">CREAMS</div>
                    <div class="header-subtitle">Community-based REhAbilitation Management System</div>
                    <div class="header-info">{{ $centre_name ?? 'CREAMS Centre' }}</div>
                @endif
            </div>
        </div>

        <!-- Letter Content -->
        <div class="letter-content">
            <!-- Reference Number -->
            <div class="letter-reference">
                Ref: {{ $letter->letter_reference }}
            </div>

            <!-- Date -->
            <div class="letter-date">
                {{ \Carbon\Carbon::parse($letter->letter_date)->format('F j, Y') }}
            </div>

            <!-- Recipient Information -->
            <div class="recipient-info">
                <div class="recipient-label">To:</div>
                <div class="recipient-details">
                    <strong>{{ $data['recipient_name'] }}</strong><br>
                    @if(!empty($data['recipient_address']))
                        {{ $data['recipient_address'] }}
                    @endif
                </div>
            </div>

            <!-- Subject -->
            <div class="letter-subject">
                Subject: {{ $letter->letter_subject }}
            </div>

            <!-- Letter Body -->
            <div class="letter-body">
                {!! nl2br(e($letter->letter_content)) !!}
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <p>Yours sincerely,</p>
                
                <div class="signature-line">
                    <div class="signature-name">{{ $user_name }}</div>
                    <div class="signature-title">{{ $user_role }}</div>
                    <div class="signature-title">{{ $centre_name ?? 'CREAMS Centre' }}</div>
                </div>
            </div>
        </div>

        <!-- Footer Section -->
        <div class="letter-footer">
            @if($footer_image)
                <div class="footer-image">
                    <img src="{{ $footer_image }}" alt="Footer Image">
                </div>
            @endif
            
            <div class="footer-content">
                @if($template && $template->footer_content)
                    {!! $footer_content !!}
                @else
                    <p>This is a computer-generated letter from CREAMS</p>
                    <p>Generated on {{ $generated_date }} at {{ now()->format('g:i A') }}</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>