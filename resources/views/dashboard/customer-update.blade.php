@extends('layouts.app')

@section('title', 'Update Your Information')

@section('content')
<div class="container">

    <div style="position: absolute; top: 0; right: 0;">
            <img src="{{ asset('images/ibedc-logo.png') }}" alt="IBEDC Logo" style="height: 60px;">
        </div>
    <h2 class="mb-4">Update Your Information</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('customer.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @if($type === 'postpaid')
            <div class="mb-3">
                <label>Account Number</label>
                <input type="text" class="form-control" value="{{ $customer->AccountNo }}" readonly>
            </div>
            <div class="mb-3">
                <label>First Name</label>
                <input type="text" name="name" class="form-control" value="{{ $customer->FirstName }}" required>
            </div>
            <div class="mb-3">
                <label>Surname</label>
                <input type="text" name="Surname" class="form-control" value="{{ $customer->Surname }}" required>
            </div>
            <div class="mb-3">
                <label>Address Line 1</label>
                <input type="text" name="address1" class="form-control" value="{{ $customer->Address1 }}" required>
            </div>
            <div class="mb-3">
                <label>Address Line 2</label>
                <input type="text" name="address2" class="form-control" value="{{ $customer->Address2 }}">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ $customer->Email ?? $customer->email ?? '' }}">    
            </div>
        @else
            <div class="mb-3">
                <label>Meter Number</label>
                <input type="text" class="form-control" value="{{ $customer->MeterNo }}" readonly>
            </div>
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ $customer->Surname }}" required>
            </div>
            <div class="mb-3">
                <label>Address</label>
                <input type="text" name="address" class="form-control" value="{{ $customer->Address }}" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ $customer->Email ?? $customer->email ?? '' }}">
            </div>    
        @endif

        <div class="mb-3">
            <label>Phone Number</label>
            <input type="text" name="phone" class="form-control" value="{{ $customer->Mobile ?? $customer->Phone }}">
        </div>

        {{-- <div class="mb-3">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" value="{{ $customer->Email }}">
        </div> --}}

        <div class="mb-3">
            <label>State of Residence</label>
            <input type="text" name="state" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>NIN</label>
            <input type="text" name="nin" class="form-control" placeholder="Enter your 11-digit NIN" required>
        </div>

        <div class="mb-3">
            <label>Occupancy Status</label>
            <select name="occupancy_status" id="occupancy_status" class="form-control" required>
                <option value="">-- Select --</option>
                <option value="landlord">Landlord</option>
                <option value="tenant">Tenant</option>
            </select>
        </div>

        <div id="tenantFields" style="display: none;">
            <div class="mb-3">
                <label>Tenant NIN</label>
                <input type="text" name="tenant_nin" class="form-control">
            </div>

            <div class="mb-3">
                <label>Do you know your Landlord?</label>
                <select name="knows_landlord" id="knows_landlord" class="form-control">
                    <option value="">-- Select --</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>
            </div>

            <div id="landlordFields" style="display: none;">
                <div class="mb-3">
                    <label>Landlord Full Name</label>
                    <input type="text" name="landlord_name" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Landlord Phone Number</label>
                    <input type="text" name="landlord_phone" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Landlord Address</label>
                    <input type="text" name="landlord_address" class="form-control">
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label>Upload NIN Document</label>
            <input type="file" name="supporting_doc" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Submit KYC Update</button>
    </form>
</div>

<script>
    const occStatus = document.getElementById('occupancy_status');
    const tenantFields = document.getElementById('tenantFields');
    const knowsLandlord = document.getElementById('knows_landlord');
    const landlordFields = document.getElementById('landlordFields');

    occStatus.addEventListener('change', function () {
        tenantFields.style.display = this.value === 'tenant' ? 'block' : 'none';
    });

    knowsLandlord?.addEventListener('change', function () {
        landlordFields.style.display = this.value === 'yes' ? 'block' : 'none';
    });
</script>
@endsection
