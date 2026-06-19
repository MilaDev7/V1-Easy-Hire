<!DOCTYPE html>
<html>
<head>
    <title>Password Reset OTP</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <div style="max-width: 480px; margin: 0 auto;">
        <h2 style="color: #198754;">EasyHire</h2>
        <p>Hi {{ $userName }},</p>
        <p>Use the OTP below to reset your password. It expires in 10 minutes.</p>
        <div style="background: #f8f9fa; padding: 16px; border-radius: 8px; text-align: center; font-size: 28px; letter-spacing: 6px; font-weight: 700; color: #198754;">
            {{ $otp }}
        </div>
        <p style="color: #6c757d; font-size: 13px; margin-top: 20px;">
            If you did not request this, ignore this email.
        </p>
    </div>
</body>
</html>
