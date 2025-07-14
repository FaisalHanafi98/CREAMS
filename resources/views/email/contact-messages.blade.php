<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset("css/contactmessagestyle.css") }}">
</head>
<body>
    
    <div class="container">
        <div class="header">
            <h1>New Contact Form Submission</h1>
        </div>
        <div class="content">
            <p><strong>Name:</strong> {{ $data['name'] }}</p>
            <p><strong>Email:</strong> {{ $data['email'] }}</p>
            
            @if(isset($data['phone']) && $data['phone'])
                <p><strong>Phone:</strong> {{ $data['phone'] }}</p>
            @endif
            
            <p><strong>Reason:</strong> {{ ucfirst($data['reason']) }}</p>
            
            <p><strong>Message:</strong><br>
            {{ $data['message'] }}</p>
            
            <p><em>Submitted on: {{ now()->format('d-m-Y H:i:s') }}</em></p>
        </div>
    </div>
</body>
</html>