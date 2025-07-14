<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-wrapper {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(-135deg, #32bdea, #c850c0);
            color: #ffffff;
            padding: 20px;
            text-align: centre;
        }
        .email-header h1 {
            font-size: 24px;
            margin: 0;
        }
        .email-body {
            padding: 30px;
        }
        .email-body h2 {
            color: #333333;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 20px;
        }
        .email-footer {
            background-color: #f5f5f5;
            padding: 15px;
            text-align: centre;
            font-size: 12px;
            color: #777777;
        }
        .button {
            display: inline-block;
            background: linear-gradient(-135deg, #32bdea, #c850c0);
            color: #ffffff;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 500;
            margin: 20px 0;
            text-align: centre;
        }
        .note {
            font-size: 12px;
            color: #777777;
            margin-top: 20px;
        }
        .help-text {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
        }
        
        @media only screen and (max-width: 600px) {
            .container {
                padding: 10px;
            }
            .email-body {
                padding: 20px;
            }
            .email-header h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-wrapper">
            <div class="email-header">
                <h1>Password Reset Request</h1>
            </div>
            
            <div class="email-body">
                <h2>Hello {{ $name ?? 'User' }},</h2>
                
                <p>You are receiving this email because we received a password reset request for your account.</p>
                
                <p>Please click the button below to reset your password:</p>
                
                <p style="text-align: centre;">
                    <a href="{{ url('reset-password', $token) }}" class="button">Reset Password</a>
                </p>
                
                <p>This password reset link will expire in 60 minutes.</p>
                
                <p>If you did not request a password reset, no further action is required.</p>
                
                <div class="help-text">
                    <p>If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
                    <p style="word-break: break-all; color: #777777;">{{ url('reset-password', $token) }}</p>
                </div>
                
                <p class="note">
                    If you continue to have problems, please contact our support team.
                </p>
            </div>
            
            <div class="email-footer">
                <p>&copy; {{ date('Y') }} CREAMS - Community-based REhAbilitation Management System. All rights reserved.</p>
                <p>Disability Services Unit (DSU), IIUM, 53100 Kuala Lumpur</p>
            </div>
        </div>
    </div>
</body>
</html>