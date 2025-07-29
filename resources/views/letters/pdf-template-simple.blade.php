<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Letter - {{ $letter->letter_reference ?? 'N/A' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            margin: 20px;
        }
        .letter-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .letter-date {
            text-align: right;
            margin-bottom: 20px;
        }
        .letter-reference {
            margin-bottom: 20px;
            font-weight: bold;
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
            margin-bottom: 40px;
            text-align: justify;
        }
        .letter-signature {
            margin-top: 40px;
        }
        .letter-footer {
            text-align: center;
            margin-top: 50px;
            border-top: 1px solid #ccc;
            padding-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="letter-container">
        <!-- Header -->
        <div class="letter-header">
            <h2>CREAMS - Community-based Rehabilitation Management System</h2>
            <p>International Islamic University Malaysia (IIUM)</p>
        </div>

        <!-- Date -->
        <div class="letter-date">
            Date: {{ isset($letter->letter_date) ? \Carbon\Carbon::parse($letter->letter_date)->format('d F Y') : \Carbon\Carbon::now()->format('d F Y') }}
        </div>

        <!-- Reference -->
        <div class="letter-reference">
            Ref: {{ $letter->letter_reference ?? 'N/A' }}
        </div>

        <!-- Recipient -->
        <div class="letter-recipient">
            @php
                $letterData = [];
                if (isset($letter->letter_data)) {
                    if (is_array($letter->letter_data)) {
                        $letterData = $letter->letter_data;
                    } else {
                        try {
                            $letterData = json_decode($letter->letter_data, true) ?? [];
                        } catch (Exception $e) {
                            $letterData = [];
                        }
                    }
                }
            @endphp
            
            @if(isset($letterData['recipient_name']) && $letterData['recipient_name'])
                <strong>{{ $letterData['recipient_name'] }}</strong><br>
            @endif
            
            @if(isset($letterData['recipient_address']) && $letterData['recipient_address'])
                {!! nl2br(e($letterData['recipient_address'])) !!}
            @endif
        </div>

        <!-- Subject -->
        @if(isset($letter->letter_subject) && $letter->letter_subject)
        <div class="letter-subject">
            RE: {{ $letter->letter_subject }}
        </div>
        @endif

        <!-- Content -->
        <div class="letter-content">
            @if(isset($letter->letter_content) && $letter->letter_content)
                {!! nl2br(e($letter->letter_content)) !!}
            @else
                <p>Letter content not available.</p>
            @endif
        </div>

        <!-- Signature -->
        <div class="letter-signature">
            <p>Thank you for your attention.</p>
            <br><br>
            <p>
                <strong>{{ $letterData['generated_by_name'] ?? session('name', 'Administrator') }}</strong><br>
                {{ $letterData['generated_by_position'] ?? 'Administrator' }}<br>
                CREAMS System - IIUM
            </p>
        </div>

        <!-- Footer -->
        <div class="letter-footer">
            <p>This letter was generated automatically by the CREAMS system.</p>
            <p>Generated on: {{ \Carbon\Carbon::now()->format('d F Y, g:i A') }}</p>
        </div>
    </div>
</body>
</html>