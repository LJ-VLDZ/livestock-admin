<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verify Your Email</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8fbf8; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; border-top: 4px solid #1B5E20;">
        <h2 style="color: #0A3622;">Welcome to Livestock Express!</h2>
        <p>Hello {{ $user->name }},</p>
        <p>Thank you for registering with Livestock Express in Alaminos City. Please click the button below to verify your email address and activate your account:</p>
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $verifyUrl }}" style="background-color: #1B5E20; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold;">Verify Email Address</a>
        </div>
        <p style="color: #666; font-size: 13px;">If you didn’t request this registration, you can safely ignore this email.</p>
        <p style="color: #1B5E20; font-weight: bold; margin-top: 20px;">The Livestock Express Team</p>
    </div>
</body>
</html>