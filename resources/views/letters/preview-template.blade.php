{{-- Simple Letter Preview Template --}}
<div class="letter-preview p-4" style="font-family: 'Times New Roman', serif; line-height: 1.6;">
    @if(isset($letter))
        {{-- Letter Header Section --}}
        <div class="text-center mb-4">
            @if(isset($template) && $template && $template->template_variables)
                @php
                    $variables = is_string($template->template_variables) 
                        ? json_decode($template->template_variables, true) 
                        : $template->template_variables;
                @endphp
                
                @if(isset($variables['header_image']) && Storage::exists('public/template_images/' . $variables['header_image']))
                    <img src="{{ asset('storage/template_images/' . $variables['header_image']) }}" 
                         alt="Header" 
                         style="max-width: 100%; height: auto; max-height: 150px;">
                @endif
            @endif
        </div>

        {{-- Letter Date and Reference --}}
        <div class="text-right mb-3">
            <strong>Date:</strong> 
            @if(isset($letter->letter_date))
                @if($letter->letter_date instanceof \Carbon\Carbon)
                    {{ $letter->letter_date->format('d F Y') }}
                @else
                    {{ date('d F Y', strtotime($letter->letter_date)) }}
                @endif
            @else
                {{ date('d F Y') }}
            @endif
        </div>

        <div class="mb-3">
            <strong>Ref:</strong> {{ $letter->letter_reference ?? 'PREVIEW' }}
        </div>

        {{-- Recipient Section --}}
        <div class="mb-4">
            @php
                $letterData = [];
                if (isset($letter->letter_data)) {
                    if (is_array($letter->letter_data)) {
                        $letterData = $letter->letter_data;
                    } elseif (is_string($letter->letter_data)) {
                        $letterData = json_decode($letter->letter_data, true) ?: [];
                    }
                }
            @endphp
            
            <strong>{{ $letterData['recipient_name'] ?? 'Recipient Name' }}</strong><br>
            @if(!empty($letterData['recipient_address']))
                {!! nl2br(e($letterData['recipient_address'])) !!}
            @endif
        </div>

        {{-- Subject --}}
        <div class="mb-4">
            <strong>RE: {{ $letter->letter_subject ?? 'Letter Subject' }}</strong>
        </div>

        {{-- Letter Content --}}
        <div class="mb-5" style="text-align: justify;">
            @if(isset($letter->letter_content))
                {!! nl2br(e($letter->letter_content)) !!}
            @else
                <p>Letter content will appear here.</p>
            @endif
        </div>

        {{-- Signature Section --}}
        <div class="mt-5">
            <p>Thank you.</p>
            <br><br>
            <div>
                <strong>{{ $letterData['generated_by_name'] ?? session('name') ?? 'Administrator' }}</strong><br>
                {{ $letterData['generated_by_position'] ?? ucfirst(session('role')) ?? 'Position' }}<br>
                CREAMS System
            </div>
        </div>

        {{-- Footer Section --}}
        @if(isset($template) && isset($variables['footer_image']) && Storage::exists('public/template_images/' . $variables['footer_image']))
            <div class="text-center mt-5">
                <img src="{{ asset('storage/template_images/' . $variables['footer_image']) }}" 
                     alt="Footer" 
                     style="max-width: 100%; height: auto; max-height: 100px;">
            </div>
        @endif
    @else
        {{-- Error State --}}
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Unable to generate preview. Please ensure all required fields are filled.
        </div>
    @endif
</div>

<style>
.letter-preview {
    background: white;
    min-height: 400px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}
</style>