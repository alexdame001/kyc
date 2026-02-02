@component('mail::message')
# Hello {{ $staffName }},

We hope you're doing well.

This is a friendly reminder that you currently have **{{ $pendingCount }} pending KYC update request(s)** assigned to you for review.

These requests are from customers in your business unit(s):  
**{{ $businessUnits }}**

Please take a moment to log in and review them at your earliest convenience.

@component('mail::button', ['url' => $dashboardUrl, 'color' => 'primary'])
    Go to My Dashboard
@endcomponent

Your timely review helps us keep customer records accurate and up-to-date.

Thank you for your prompt attention!

Best regards,  
**IBEDC KYC Team**

@endcomponent