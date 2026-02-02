@component('mail::message')
<p style="text-align: center;">
    <img src="{{ asset('images/ibedc-logo.png') }}" alt="IBEDC Logo" style="max-width: 180px; margin-bottom: 20px;">
</p>

# Dear {{ $user->name }},

Your staff account on **IBEDC KYC Application** has been successfully created.

---

### 🔑 Login Details
- **Email/Username:** {{ $user->email }}
- **Password:** 123456  
  *(You will be required to change this upon first login)*  
- **Role:** {{ strtoupper($user->role) }}

---

@component('mail::button', ['url' => 'https://kyc.ibedc.com/staff'])
Login to Staff Portal
@endcomponent

If the button above does not work, copy and paste this link into your browser:  
[https://kyc.ibedc.com/staff](https://kyc.ibedc.com/staff)

---

Thanks,  
**IBEDC KYC Team**  
@endcomponent
