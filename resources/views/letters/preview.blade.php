{{-- Letter Preview Template --}}
<div class="letter-preview" style="max-width: 800px; margin: 0 auto; font-family: 'Times New Roman', serif; line-height: 1.6; background: white; padding: 40px; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
    {{-- Header Section --}}
    @if($template && $template->header_image)
        <div class="letter-header text-center mb-4">
            <img src="{{ $template->header_image_url }}" alt="Letterhead" style="max-width: 100%; height: auto; max-height: 150px;">
        </div>
    @endif
    
    @if($template && $template->header_content)
        <div class="header-content text-center mb-4" style="font-size: 14px; color: #666;">
            {!! nl2br(e($template->header_content)) !!}
        </div>
    @endif
    
    {{-- Date --}}
    <div class="letter-date text-right mb-4" style="font-size: 14px;">
        {{ $letter_date }}
    </div>
    
    {{-- Recipient Details --}}
    <div class="recipient-details mb-4" style="font-size: 14px;">
        <strong>{{ $recipient_name }}</strong>
        @if(!empty($recipient_address))
            <br>{{ nl2br(e($recipient_address)) }}
        @endif
    </div>
    
    {{-- Subject --}}
    <div class="letter-subject mb-4" style="font-weight: bold; font-size: 16px; text-decoration: underline;">
        Subject: {{ $subject }}
    </div>
    
    {{-- Content --}}
    <div class="letter-content mb-5" style="font-size: 14px; text-align: justify;">
        {!! nl2br(e($content)) !!}
    </div>
    
    {{-- Footer Section --}}
    @if($template && $template->footer_content)
        <div class="footer-content mt-5 pt-3" style="border-top: 1px solid #eee; font-size: 12px; color: #666; text-align: center;">
            {!! nl2br(e($template->footer_content)) !!}
        </div>
    @endif
    
    @if($template && $template->footer_image)
        <div class="letter-footer text-center mt-4">
            <img src="{{ $template->footer_image_url }}" alt="Footer" style="max-width: 100%; height: auto; max-height: 100px;">
        </div>
    @endif
</div>

<style>
.letter-preview {
    background: white;
    color: black;
}

.letter-preview * {
    color: black !important;
}

@media print {
    .letter-preview {
        box-shadow: none;
        padding: 20px;
    }
}
</style>