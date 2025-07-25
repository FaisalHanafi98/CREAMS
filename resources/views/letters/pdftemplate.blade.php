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
        <!-- Header Image -->
        @if(isset($headerImage) && $headerImage)
            <div class="header-image">
                <img src="data:image/png;base64,{{ $headerImage }}" alt="Header">
            </div>
        @endif

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

        <!-- Footer Image -->
        @if(isset($footerImage) && $footerImage)
            <div class="footer-image">
                <img src="data:image/png;base64,{{ $footerImage }}" alt="Footer">
            </div>
        @endif
    </div>
</body>
</html>