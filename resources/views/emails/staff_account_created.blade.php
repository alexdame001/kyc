<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>KYC Account Created</title>
</head>
<body>
    <p>Dear {{ $name }},</p>

    <p>Your Account on <strong>KYC APPLICATION</strong> has been created.</p>

    <p><strong>Your Login details are as follows:</strong></p>
    <ul>
        <li>Email/Username: {{ $email }}</li>
        <li>Password: <em>Default password (123456)</em> <br>
            A password update will be required for first login.</li>
        <li>Role: {{ $role }}</li>
    </ul>

    <p>You may proceed to update the password for your account after login.</p>

    <p>
        Visit <a href="https://kyc.ibedc.com/staff">https://kyc.ibedc.com/staff</a> to login.
    </p>

    <p>Thanks, <br> IBEDC KYC Team</p>
</body>
</html>
