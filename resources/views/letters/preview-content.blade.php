<div class="letter-preview" style="background: white; padding: 40px; font-family: 'Times New Roman', serif; line-height: 1.6; max-width: 800px; margin: 0 auto; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <!-- Header -->
    <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #007bff; padding-bottom: 20px;">
        <h2 style="margin: 0; color: #007bff;">CREAMS - Community-based REhAbilitation Management System</h2>
        <p style="margin: 5px 0; color: #666;">{{ $previewData['centre_name'] }}</p>
    </div>

    <!-- Date -->
    <div style="text-align: right; margin-bottom: 20px;">
        <strong>Date:</strong> {{ Carbon\Carbon::parse($previewData['letter_date'])->format('d F Y') }}
    </div>

    <!-- Reference -->
    <div style="margin-bottom: 20px;">
        <strong>Ref:</strong> {{ $previewData['letter_reference'] }}
    </div>

    <!-- Recipient -->
    <div style="margin-bottom: 30px;">
        <strong>{{ $previewData['recipient_name'] }}</strong><br>
        @if(!empty($previewData['recipient_address']))
            {!! nl2br(e($previewData['recipient_address'])) !!}
        @endif
    </div>

    <!-- Subject -->
    <div style="font-weight: bold; margin: 30px 0; text-decoration: underline;">
        RE: {{ strtoupper($previewData['letter_subject']) }}
    </div>

    <!-- Content -->
    <div style="text-align: justify; margin-bottom: 50px; white-space: pre-wrap;">
        {!! nl2br(e($previewData['letter_content'])) !!}
    </div>

    <!-- Signature -->
    <div style="margin-top: 80px;">
        <p>Thank you.</p>
        
        <div style="margin-top: 60px;">
            <p>
                <strong>{{ $previewData['generated_by_name'] }}</strong><br>
                {{ ucfirst($previewData['generated_by_position']) }}<br>
                {{ $previewData['centre_name'] }}
            </p>
        </div>
    </div>
    
    <!-- Preview Notice -->
    <div style="background: #f8f9fa; border: 2px dashed #007bff; padding: 15px; margin-top: 30px; text-align: center; color: #007bff;">
        <strong><i class="fas fa-eye"></i> PREVIEW MODE</strong><br>
        <small>This is how your letter will appear in the final PDF</small>
    </div>
</div>