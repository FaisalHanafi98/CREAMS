@if(isset($letter))
<div class="letter-preview">
    <style>
        .letter-preview {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        .letter-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .letter-date {
            text-align: right;
            margin-bottom: 30px;
        }
        .letter-recipient {
            margin-bottom: 30px;
        }
        .letter-subject {
            font-weight: bold;
            margin: 30px 0;
        }
        .letter-content {
            text-align: justify;
            margin-bottom: 40px;
            white-space: pre-wrap;
        }
        .letter-signature {
            margin-top: 60px;
        }
        .letter-footer {
            text-align: center;
            margin-top: 40px;
        }
    </style>

    <!-- Header Image -->
    @if(isset($template) && $template->template_variables)
        @php
            $variables = is_string($template->template_variables) 
                ? json_decode($template->template_variables, true) 
                : $template->template_variables;
        @endphp
        
        @if(isset($variables['header_image']))
            <div class="letter-header">
                <img src="{{ asset('storage/template_images/' . $variables['header_image']) }}" 
                     alt="Header" 
                     style="max-width: 100%; height: auto;">
            </div>
        @endif
    @endif

    <!-- Letter Date -->
    <div class="letter-date">
        <strong>Date:</strong> {{ $letter->letter_date instanceof \Carbon\Carbon ? $letter->letter_date->format('d F Y') : \Carbon\Carbon::parse($letter->letter_date)->format('d F Y') }}
    </div>

    <!-- Reference Number -->
    <div class="letter-reference">
        <strong>Ref:</strong> {{ $letter->letter_reference }}
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
        <br><br>
        <p>
            <strong>{{ $letterData['generated_by_name'] ?? 'Administrator' }}</strong><br>
            {{ $letterData['generated_by_position'] ?? 'Position' }}<br>
            CREAMS System
        </p>
    </div>

    <!-- Footer Image -->
    @if(isset($template) && isset($variables['footer_image']))
        <div class="letter-footer">
            <img src="{{ asset('storage/template_images/' . $variables['footer_image']) }}" 
                 alt="Footer" 
                 style="max-width: 100%; height: auto;">
        </div>
    @endif
</div>
@else
<div class="alert alert-danger">
    Error: Letter data not found. Please try again.
</div>
@endif