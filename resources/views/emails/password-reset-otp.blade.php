<!-- <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset OTP</title>
</head>
<body>
    <p>Hello {{ $user->first_name ?? 'Customer' }},</p>
    <p>Your password reset one-time code is: <strong>{{ $otp }}</strong></p>
    <p>This code will expire in 10 minutes. Please do not share it with anyone.</p>
    <p>If you did not request a password reset, no further action is required.</p>
    <br />
    <p>Regards,<br>
    IBEDC Debt Recovery Team</p>
</body>
</html> -->


<!DOCTYPE html>
<html>
<head>
    <title>Password Reset OTP</title>
</head>
<body>
    <p>Hello,</p>
    <p>Your OTP for password reset is: <strong>{{ $otp }}</strong></p>
    <p>This code will expire in 10 minutes.</p>
</body>
</html>

