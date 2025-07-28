<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - CREAMS</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #32bdea;
            margin: 0;
            font-size: 28px;
        }
        .header h2 {
            color: #c850c0;
            margin: 5px 0 0;
            font-size: 18px;
            font-weight: normal;
        }
        .content {
            padding: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #32bdea, #c850c0);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 12px;
        }
        .note {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            font-size: 14px;
            border-left: 4px solid #32bdea;
        }
        @media only screen and (max-width: 620px) {
            .container {
                width: 100%;
                padding: 15px;
            }
            .button {
                display: block;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CREAMS</h1>
            <h2>Community-based REhAbilitation Management System</h2>
        </div>
        <div class="content">
            <h2>Hello, {{ $data['name'] }}</h2>
            
            <p>You are receiving this email because we received a password reset request for your account.</p>
            
            <p>Please click the button below to reset your password:</p>
            
            <a href="{{ $data['resetUrl'] }}" class="button">Reset Password</a>
            
            <div class="note">
                <p>If you did not request a password reset, no further action is required.</p>
                <p>This password reset link will expire in 60 minutes.</p>
            </div>
            
            <p>If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
            
            <p style="word-break: break-all;">{{ $data['resetUrl'] }}</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} CREAMS - All rights reserved.</p>
            <p>Disability Services Unit (DSU), IIUM, 53100 Kuala Lumpur</p>
        </div>
    </div>
</body>
</html>