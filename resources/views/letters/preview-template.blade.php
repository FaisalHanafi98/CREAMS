<div class="letter-preview" style="max-width: 800px; margin: 0 auto; padding: 20px; font-family: 'Times New Roman', serif; background: white; border: 1px solid #ddd;">
    <!-- Reference Number -->
    <div style="text-align: right; font-size: 12px; color: #666; margin-bottom: 20px; font-weight: bold;">
        Ref: {{ $letter->reference_number }}
    </div>
    
    <!-- Header Section -->
    @if($template && $template->header_image)
        <div style="text-align: center; margin-bottom: 25px; border-bottom: 2px solid #000; padding-bottom: 15px;">
            <img src="{{ $template->header_image_url }}" alt="Header" style="max-width: 100%; max-height: 120px;">
        </div>
    @endif
    
    @if($template && $template->header_content)
        <div style="text-align: center; font-size: 11px; margin-bottom: 20px; color: #333;">
            {!! nl2br(e($template->header_content)) !!}
        </div>
    @endif
    
    <!-- Letter Date -->
    <div style="text-align: right; margin-bottom: 25px; font-size: 12px;">
        {{ \Carbon\Carbon::parse($letter->letter_date)->format('d F Y') }}
    </div>
    
    <!-- Recipient Information -->
    <div style="margin-bottom: 25px; line-height: 1.4;">
        <div style="font-weight: bold; font-size: 13px; margin-bottom: 5px;">
            {{ $letter->recipient_name }}
        </div>
        @if($letter->recipient_address)
            <div style="font-size: 12px; color: #333;">
                {!! nl2br(e($letter->recipient_address)) !!}
            </div>
        @endif
    </div>
    
    <!-- Subject Line -->
    <div style="font-weight: bold; margin: 25px 0; font-size: 13px; text-decoration: underline;">
        Subject: {{ $letter->subject }}
    </div>
    
    <!-- Greeting -->
    <div style="margin-bottom: 20px; font-size: 12px;">
        Dear {{ $letter->recipient_name }},
    </div>
    
    <!-- Letter Content -->
    <div style="text-align: justify; margin: 25px 0; white-space: pre-wrap; line-height: 1.6; font-size: 12px;">
        {!! nl2br(e($letter->content)) !!}
    </div>
    
    <!-- Signature Section -->
    <div style="margin-top: 40px; font-size: 12px;">
        <div style="margin-bottom: 15px;">
            Yours sincerely,
        </div>
        
        <div style="margin: 30px 0 10px 0;">
            <!-- Space for signature -->
            <div style="height: 40px;"></div>
        </div>
        
        <div>
            <div style="font-weight: bold; border-top: 1px solid #000; padding-top: 5px; display: inline-block; min-width: 200px;">
                {{ $letter->generated_by_name }}
            </div>
            <div style="font-size: 11px; margin-top: 5px;">
                {{ $letter->generated_by_position }}
            </div>
            <div style="font-size: 11px; margin-top: 2px; font-style: italic;">
                {{ config('app.name', 'CREAMS') }}
            </div>
        </div>
    </div>
    
    <!-- Footer Section -->
    @if($template && ($template->footer_image || $template->footer_content))
        <div style="text-align: center; margin-top: 40px; padding-top: 15px; border-top: 1px solid #ccc; font-size: 10px; color: #666;">
            @if($template->footer_image)
                <img src="{{ $template->footer_image_url }}" alt="Footer" style="max-width: 100%; max-height: 60px; margin-bottom: 5px;">
            @endif
            
            @if($template->footer_content)
                <div style="margin-top: 5px;">
                    {!! nl2br(e($template->footer_content)) !!}
                </div>
            @endif
        </div>
    @endif
    
    <!-- Preview Notice -->
    <div style="position: fixed; top: 10px; right: 10px; background: #ff9800; color: white; padding: 5px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; z-index: 1000;">
        PREVIEW
    </div>
</div>

<style>
/* Additional styles for the preview */
.letter-preview {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-radius: 5px;
}

/* Print styles for preview */
@media print {
    .letter-preview {
        border: none;
        box-shadow: none;
        margin: 0;
        padding: 0;
    }
}
</style>