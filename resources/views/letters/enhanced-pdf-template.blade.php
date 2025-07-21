<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $letter->letter_reference }}</title>
    <style>
        @page {
            margin: 2cm;
            size: A4 portrait;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header img {
            max-height: 120px;
            width: auto;
        }
        
        .letter-meta {
            margin-bottom: 30px;
        }
        
        .letter-date {
            text-align: right;
            margin-bottom: 20px;
        }
        
        .letter-reference {
            margin-bottom: 20px;
        }
        
        .recipient {
            margin-bottom: 30px;
        }
        
        .subject {
            font-weight: bold;
            margin: 30px 0;
            text-decoration: underline;
        }
        
        .content {
            text-align: justify;
            margin-bottom: 50px;
            white-space: pre-wrap;
        }
        
        .signature {
            margin-top: 80px;
        }
        
        .signature-block {
            margin-top: 60px;
        }
        
        .footer {
            position: fixed;
            bottom: 2cm;
            left: 2cm;
            right: 2cm;
            text-align: center;
        }
        
        .footer img {
            max-height: 80px;
            width: auto;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header Image -->
    @if($headerImage)
        <div class="header">
            <img src="{{ $headerImage }}" alt="Header">
        </div>
    @endif

    <!-- Letter Content -->
    <div class="letter-body">
        <!-- Date -->
        <div class="letter-date">
            <strong>Date:</strong> {{ Carbon\Carbon::parse($letter->letter_date)->format('d F Y') }}
        </div>

        <!-- Reference -->
        <div class="letter-reference">
            <strong>Ref:</strong> {{ $letter->letter_reference }}
        </div>

        <!-- Recipient -->
        <div class="recipient">
            <strong>{{ $letterData['recipient_name'] }}</strong><br>
            @if(!empty($letterData['recipient_address']))
                {!! nl2br(e($letterData['recipient_address'])) !!}
            @endif
        </div>

        <!-- Subject -->
        <div class="subject">
            RE: {{ strtoupper($letter->letter_subject) }}
        </div>

        <!-- Content -->
        <div class="content">
            {!! nl2br(e($letter->letter_content)) !!}
        </div>

        <!-- Signature -->
        <div class="signature">
            <p>Thank you.</p>
            
            <div class="signature-block">
                <p>
                    <strong>{{ $letterData['generated_by_name'] }}</strong><br>
                    {{ ucfirst($letterData['generated_by_position']) }}<br>
                    {{ $letterData['centre_name'] ?? 'CREAMS System' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Footer Image -->
    @if($footerImage)
        <div class="footer">
            <img src="{{ $footerImage }}" alt="Footer">
        </div>
    @endif
</body>
</html>