<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IBEDC - Customer Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      overflow: hidden;
    }

    /* Background slideshow */
    .bg-slide {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-size: cover;
      background-position: center;
      animation: fade 20s infinite;
      z-index: -1;
    }

    .bg1 { background-image: url('https://media.gettyimages.com/id/1460253162/nl/vector/kyc-on-a-mobile-phone.jpg?s=612x612&w=0&k=20&c=pNPZWBjh9cMvMVdQdQdpT13kE5NhBc909qFRCuIgveU='); animation-delay: 0s; }
    .bg2 { background-image: url('https://media.gettyimages.com/id/1360510722/nl/foto/young-man-paying-with-credit-card-on-his-phone-online-payment-buying-crypto-currencies.jpg?s=612x612&w=0&k=20&c=AG3lnisxOA7X_cY3uAj9aOV7_a2eqxfzBa17rG_w_vU='); animation-delay: 10s; }

    @keyframes fade {
      0%, 40% { opacity: 1; }
      50%, 90% { opacity: 0; }
      100% { opacity: 1; }
    }

    .login-container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100%;
      z-index: 1;
      position: relative;
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
      background: rgba(255, 255, 255, 0.95);
      padding: 2rem;
      width: 100%;
      max-width: 420px;
    }

    .logo {
      width: 130px;
      display: block;
      margin: 0 auto 15px;
    }

    .btn-login {
      background-color: #f58220; /* IBEDC orange */
      color: white;
      font-weight: 600;
      border-radius: 8px;
    }
    .btn-login:hover {
      background-color: #d96d0c;
    }
  </style>
</head>
<body>

  <!-- Moving Background -->
  <div class="bg-slide bg1"></div>
  <div class="bg-slide bg2"></div>

  <div class="login-container">
    <div class="card">
      <!-- Logo -->
      <img src="{{ asset('images/ibedc-logo.png') }}" alt="IBEDC Logo" class="logo">

      <h3 class="text-center mb-3">Customer Login</h3>
      <p class="text-center text-muted">Login with your Account Number (Postpaid) or Meter Number (Prepaid)</p>

      <!-- Flash Alerts -->
      @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
      @endif

      @if(session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
              {{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
      @endif

      <form method="POST" action="{{ route('customer.login.form') }}">
          @csrf
          <div class="mb-3">
              <label for="account_type" class="form-label">Select Account Type</label>
              <select class="form-select" name="account_type" required>
                  <option value="">-- Select --</option>
                  <option value="postpaid">Postpaid (Account Number)</option>
                  <option value="prepaid">Prepaid (Meter Number)</option>
              </select>
          </div>

          <div class="mb-3">
              <label for="account_id" class="form-label">Enter Account / Meter Number</label>
              <input type="text" class="form-control" name="account_id" required placeholder="e.g. 1234567890">
          </div>

          <button type="submit" class="btn btn-login w-100">Login</button>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
