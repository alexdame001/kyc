<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Fields to Update</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; padding: 0; width: 100%; font-family: 'Inter', sans-serif; }
        body {
            background-color: #f0f0f0;
            background-image: url('https://raw.githubusercontent.com/alexdame/nonense/main/background.jpg');
            background-size: cover; background-position: center; background-repeat: no-repeat;
        }
        .login-container { display: flex; justify-content: center; align-items: center; min-height: 100vh; width: 100%; }
        .login-card {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 2rem 2.5rem; width: 100%; max-width: 450px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(5px); border-radius: 0.5rem;
        }
        .btn-custom-orange { background-color: #ff9100; color: white; font-weight: bold; padding: 0.75rem 1.5rem; width: 100%; border-radius: 4px; border: none; }
        .btn-custom-orange:hover { background-color: #e68200; }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <h3 class="mb-3">Select Fields You Want to Update</h3>

        {{-- Warning if fields are missing --}}
        @if($missingFields->isNotEmpty())
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i>
                Some of your information is missing and <strong>must</strong> be updated:
                {{ implode(', ', $missingFields->toArray()) }}.
            </div>
        @endif

        <form action="{{ route('customer.update.occupancy.handle') }}" method="POST">
            @csrf

            <!-- Field Selection -->
            <div class="mb-4">
                <label><strong>Which fields would you like to update?</strong></label><br>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="fields[]" value="name" id="field_name"
                        @if($missingFields->contains('Name')) checked disabled @endif>
                    <label class="form-check-label" for="field_name">Name</label>
                    @if($missingFields->contains('Name'))
                        <input type="hidden" name="fields[]" value="name">
                    @endif
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="fields[]" value="address" id="field_address"
                        @if($missingFields->contains('Address')) checked disabled @endif>
                    <label class="form-check-label" for="field_address">Address</label>
                    @if($missingFields->contains('Address'))
                        <input type="hidden" name="fields[]" value="address">
                    @endif
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="fields[]" value="email" id="field_email"
                        @if($missingFields->contains('Email')) checked disabled @endif>
                    <label class="form-check-label" for="field_email">Email</label>
                    @if($missingFields->contains('Email'))
                        <input type="hidden" name="fields[]" value="email">
                    @endif
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="fields[]" value="phone" id="field_phone"
                        @if($missingFields->contains('Phone')) checked disabled @endif>
                    <label class="form-check-label" for="field_phone">Phone Number</label>
                    @if($missingFields->contains('Phone'))
                        <input type="hidden" name="fields[]" value="phone">
                    @endif
                </div>
            </div>

            <!-- Occupancy Selection -->
            <div class="mb-4">
                <label><strong>Select Your Occupancy Status</strong></label>
                <select name="occupancy_status" class="form-control" required>
                    <option value="">-- Choose Occupancy --</option>
                    <option value="landlord">Landlord</option>
                    {{-- <option value="tenant">Tenant</option> --}}
                </select>
            </div>

            <button type="submit" class="btn btn-custom-orange">Continue</button>
        </form>
    </div>
</div>
</body>
</html>
