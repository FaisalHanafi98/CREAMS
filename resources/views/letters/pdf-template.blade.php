<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Letter - {{ $letter->letter_reference }}</title>
    <style>
        @page {
            margin: 20mm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14pt;
            line-height: 1.6;
            color: #000;
        }
        
        .letter-container {
            width: 100%;
        }
        
        .header-image {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header-image img {
            max-width: 100%;
            height: auto;
            max-height: 150px;
        }
        
        .letter-date {
            text-align: right;
            margin-bottom: 20px;
        }
        
        .letter-reference {
            margin-bottom: 20px;
        }
        
        .letter-recipient {
            margin-bottom: 30px;
        }
        
        .letter-subject {
            font-weight: bold;
            margin: 30px 0;
            text-decoration: underline;
        }
        
        .letter-content {
            text-align: justify;
            margin-bottom: 40px;
            white-space: pre-wrap;
        }
        
        .letter-signature {
            margin-top: 60px;
        }
        
        .footer-image {
            position: fixed;
            bottom: 20mm;
            left: 0;
            right: 0;
            text-align: center;
        }
        
        .footer-image img {
            max-width: 100%;
            height: auto;
            max-height: 100px;
        }
    </style>
</head>
<body>
    <div class="letter-container">
        <!-- Header Section -->
        <div class="header-image">
            @if(isset($headerImage) && $headerImage && !empty($headerImage))
                <img src="{{ $headerImage }}" alt="Header">
            @elseif(isset($template) && $template && isset($template->template_variables) && isset($template->template_variables['header_content']))
                <!-- Template Header Content -->
                <div style="text-align: center; padding: 20px; border-bottom: 2px solid #32bdea;">
                    {!! $template->template_variables['header_content'] !!}
                </div>
            @else
                <!-- Default CREAMS Header -->
                <div style="text-align: center; padding: 20px; border-bottom: 2px solid #32bdea;">
                    <h2 style="color: #32bdea; margin: 0;">CREAMS</h2>
                    <p style="margin: 5px 0; color: #666;">Community-based REhAbilitation Management System</p>
                </div>
            @endif
        </div>

        <!-- Letter Date -->
        <div class="letter-date">
            Date: {{ $letter->letter_date instanceof \Carbon\Carbon ? $letter->letter_date->format('d F Y') : \Carbon\Carbon::parse($letter->letter_date)->format('d F Y') }}
        </div>

        <!-- Reference Number -->
        <div class="letter-reference">
            Ref: {{ $letter->letter_reference }}
        </div>

        <!-- Recipient -->
        <div class="letter-recipient">
            @php
                $letterData = is_array($letter->letter_data) ? $letter->letter_data : json_decode($letter->letter_data, true);
            @endphp
            
            @if(isset($letterData['recipient_name']))
                <strong>{{ $letterData['recipient_name'] }}</strong><br>
            @endif
            
            @if(isset($letterData['recipient_address']) && $letterData['recipient_address'])
                {!! nl2br(e($letterData['recipient_address'])) !!}
            @endif
        </div>

        <!-- Subject -->
        <div class="letter-subject">
            RE: {{ $letter->letter_subject }}
        </div>

        <!-- Content -->
        <div class="letter-content">
            {!! nl2br(e($letter->letter_content)) !!}
        </div>

        <!-- Signature -->
        <div class="letter-signature">
            <p>Thank you.</p>
            <br><br><br>
            <p>
                <strong>{{ $letterData['generated_by_name'] ?? 'Administrator' }}</strong><br>
                {{ $letterData['generated_by_position'] ?? 'Position' }}<br>
                CREAMS System
            </p>
        </div>

        <!-- Footer Section -->
        <div class="footer-image">
            @if(isset($footerImage) && $footerImage && !empty($footerImage))
                <img src="{{ $footerImage }}" alt="Footer">
            @elseif(isset($template) && $template && isset($template->template_variables) && isset($template->template_variables['footer_content']))
                <!-- Template Footer Content -->
                <div style="text-align: center; padding: 10px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">
                    {!! $template->template_variables['footer_content'] !!}
                </div>
            @else
                <!-- Default CREAMS Footer -->
                <div style="text-align: center; padding: 10px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">
                    <p>This is a computer-generated letter from CREAMS.</p>
                    <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>