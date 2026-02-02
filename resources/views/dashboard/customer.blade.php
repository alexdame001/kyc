<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IBEDC Customer Dashboard</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
*, *::before, *::after {
    box-sizing: border-box;
}

html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    width: 100%;
    font-family: 'Inter', sans-serif;
}

body {
    background-color: #f0f0f0;
    background-image: url('https://raw.githubusercontent.com/alexdame/nonense/main/background.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    width: 100%;
}

.login-card {
    background-color: rgba(255, 255, 255, 0.9);
    padding: 2rem 2.5rem;
    width: 100%;
    max-width: 450px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    border-radius: 0.5rem;
    border: 1px solid rgba(255, 255, 255, 0.3);
    display: flex;
    flex-direction: column;
    align-items: center;
}

.card-title {
    color: #000;
    font-weight: bold;
    text-align: center;
    margin-bottom: 0.5rem;
    font-size: 1.5rem;
}

.card-subtitle {
    color: #666;
    text-align: center;
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
    width: 100%;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #ccc;
    outline: none;
    border-radius: 4px;
    font-size: 1rem;
}

/* Custom Orange Button */
.btn-custom-orange {
    background-color: #ff9100;
    color: white;
    font-weight: bold;
    padding: 0.75rem 1.5rem;
    border-radius: 4px;
    transition: background-color 0.3s ease;
    border: none;
}
.btn-custom-orange:hover {
    background-color: #e68200;
}

.text-links {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    margin-top: 0.5rem;
}

.text-links a {
    color: #ff9100;
    text-decoration: none;
    font-size: 0.875rem;
}

.text-links a:hover {
    text-decoration: underline;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.remember-me input[type="checkbox"] {
    accent-color: #ff9100;
}

@media (max-width: 640px) {
    .login-card {
        padding: 1.5rem;
    }
}
</style>
</head>
<body>

<div class="container position-relative">

    {{-- Flash Message Display --}}
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
        <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Personalized Greeting --}}
    <h1 class="mt-4">
        @if (session('account_type') === 'postpaid')
            Welcome, {{ $customer->FirstName }} {{ $customer->Surname }} 
        @elseif(session('account_type') === 'prepaid')
            Welcome, {{ $customer->OtherNames }} {{ $customer->Surname }}
        @else
            Welcome, Customer
        @endif
    </h1>
    <hr>

    <h4>Customer Details</h4>

    @if (session('account_type') === 'postpaid')
        <p><strong>Account Number:</strong> {{ $customer->AccountNo }}</p>
        <p><strong>Surname:</strong> {{ $customer->Surname }}</p>
        <p><strong>First Name:</strong> {{ $customer->FirstName }}</p>
        <p><strong>Address:</strong> {{ trim(($customer->Address1 ?? '') . ' ' . ($customer->Address2 ?? '')) }}</p>
        <p><strong>Phone:</strong> {{ $customer->Mobile ?? 'N/A' }}</p>
        <p><strong>Email:</strong> {{ $customer->Email ?? 'N/A' }}</p>

    @elseif(session('account_type') === 'prepaid')
        <p><strong>Meter Number:</strong> {{ $customer->MeterNo }}</p>
        <p><strong>Surname:</strong> {{ $customer->Surname }}</p>
        <p><strong>OtherNames:</strong> {{ $customer->OtherNames }}</p>
        <p><strong>Address:</strong> {{ $customer->Address ?? 'N/A' }}</p>
        <p><strong>Phone:</strong> {{ $customer->Phone ?? 'N/A' }}</p>
        <p><strong>Email:</strong> {{ $customer->Email ?? 'N/A' }}</p>

    @else
        <p>Unknown account type.</p>
    @endif


    <div class="mb-4 text-end">
        <button type="button" class="btn btn-custom-orange" data-bs-toggle="modal" data-bs-target="#requiredDocsModal">
            Update Your Information
        </button>
    </div>

</div>


<!-- Modal -->
<div class="modal fade" id="requiredDocsModal" tabindex="-1" aria-labelledby="requiredDocsModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title" id="requiredDocsModalLabel">Information Update Requirements</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body" id="modal-content">

    <div id="main-content">
        <p><strong>Before you proceed, please be aware of the following requirements for updating your information:</p>
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>1. Name Correction:</strong>  You are required to upload your NIN slip. The corrected name must exactly match the details on your NIN.</li>
            <li class="list-group-item"><strong>2. Complete Name Change:</strong> In addition to uploading your NIN slip, you must also upload a sworn court affidavit supporting the name change.</li>
            <li class="list-group-item"><strong>3. Address Update:</strong> You are required to upload a sworn court affidavit confirming your address update.</li>
            <li class="list-group-item"><strong>4. Additional Information:</strong> You will also be required to provide your <strong>Line of Business</strong> and the <strong>Type of Building</strong>.</li>
        </ul>
        <div class="alert alert-info mt-3">
            <p class="mb-0">You agree to our Privacy Policy, which allows us to contact you in line with the NDPR.</p>
            <p class="mb-0">By clicking "Proceed", you confirm you have the necessary documents ready.</p>
        </div>
    </div>

    <div id="privacy-policy-content" class="d-none">
        <h5>Privacy Policy</h5>
        <p>Your data is collected to improve our services, manage your account, and communicate with you. We do not sell or share your personal information.</p>
        <button id="backButton" class="btn btn-secondary mt-3">Back</button>
    </div>

</div>

<div class="modal-footer">
    <div class="form-check me-auto">
        <input class="form-check-input" type="checkbox" id="privacyPolicyCheck">
        <label class="form-check-label" for="privacyPolicyCheck">
            I agree to the <button class="btn btn-link p-0 m-0" id="readMoreButton">Privacy Policy</button>
        </label>
    </div>

    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

    <!-- Proceed Button with spinner -->
    <button type="button" class="btn btn-custom-orange" id="proceedButton" disabled>
        Proceed
    </button>
</div>

</div>
</div>
</div>


<!-- Bootstrap Bundle with JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<script>
document.addEventListener('DOMContentLoaded', function() {

    const privacyPolicyCheck = document.getElementById('privacyPolicyCheck');
    const proceedButton = document.getElementById('proceedButton');
    const readMoreButton = document.getElementById('readMoreButton');
    const backButton = document.getElementById('backButton');
    const mainContent = document.getElementById('main-content');
    const privacyPolicyContent = document.getElementById('privacy-policy-content');

    // Enable/disable Proceed button
    privacyPolicyCheck.addEventListener('change', function() {
        proceedButton.disabled = !this.checked;
    });

    // Show Privacy Policy
    readMoreButton.addEventListener('click', function() {
        mainContent.classList.add('d-none');
        privacyPolicyContent.classList.remove('d-none');
    });

    // Back to main content
    backButton.addEventListener('click', function() {
        privacyPolicyContent.classList.add('d-none');
        mainContent.classList.remove('d-none');
    });

    // Redirect on Proceed with spinner
    proceedButton.addEventListener('click', function() {
        if (!this.disabled) {
            proceedButton.disabled = true;
            proceedButton.innerHTML = `
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Redirecting...
            `;
            setTimeout(() => {
                window.location.href = "{{ route('customer.update.occupancy.select') }}";
            }, 800);
        }
    });
});
</script>

</body>
</html>
