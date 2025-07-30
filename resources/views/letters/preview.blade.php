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

    <!-- Header Content -->
    @if(isset($template) && $template->template_variables)
        @php
            $variables = is_string($template->template_variables) 
                ? json_decode($template->template_variables, true) 
                : $template->template_variables;
        @endphp
        
        @if(isset($variables['header_content']) && $variables['header_content'])
            <div class="letter-header">
                <h3>{!! nl2br(e($variables['header_content'])) !!}</h3>
            </div>
        @endif
        
        @if(isset($variables['header_image']) && $variables['header_image'])
            <div class="letter-header">
                <img src="{{ asset('storage/' . $variables['header_image']) }}" 
                     alt="Header" 
                     style="max-width: 100%; height: auto;"
                     onerror="this.style.display='none';">
            </div>
        @endif
    @endif

    <!-- Letter Date -->
    <div class="letter-date">
        <strong>Date:</strong> {{ $letter_date ?? \Carbon\Carbon::now()->format('d F Y') }}
    </div>

    <!-- Recipient -->
    @if(isset($recipient_name) || isset($recipient_address))
    <div class="letter-recipient">
        @if(isset($recipient_name) && $recipient_name)
            <strong>{{ $recipient_name }}</strong><br>
        @endif
        
        @if(isset($recipient_address) && $recipient_address)
            {!! nl2br(e($recipient_address)) !!}
        @endif
    </div>
    @endif

    <!-- Subject -->
    <div class="letter-subject">
        <strong>RE: {{ $subject ?? 'Letter Subject' }}</strong>
    </div>

    <!-- Content -->
    <div class="letter-content">
        {!! nl2br(e($content ?? 'Letter content will appear here...')) !!}
    </div>

    <!-- Signature -->
    <div class="letter-signature">
        <p>Thank you.</p>
        <br><br>
        <p>
            <strong>{{ session('name') ?? 'Administrator' }}</strong><br>
            {{ ucfirst(session('role')) ?? 'Position' }}<br>
            CREAMS System
        </p>
    </div>

    <!-- Footer Content -->
    @if(isset($template) && isset($variables))
        @if(isset($variables['footer_content']) && $variables['footer_content'])
            <div class="letter-footer">
                <p><em>{!! nl2br(e($variables['footer_content'])) !!}</em></p>
            </div>
        @endif
        
        @if(isset($variables['footer_image']) && $variables['footer_image'])
            <div class="letter-footer">
                <img src="{{ asset('storage/' . $variables['footer_image']) }}" 
                     alt="Footer" 
                     style="max-width: 100%; height: auto;"
                     onerror="this.style.display='none';">
            </div>
        @endif
    @endif
</div>