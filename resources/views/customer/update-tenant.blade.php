@extends('layouts.app')

@section('title', 'Tenant KYC Update')

@section('content')
    <div class="container">
        <h2>KYC Update Form (Tenant)</h2>

        <div style="position: absolute; bottom: 0; right: 0;">
            <img src="{{ asset('images/ibedc-logo.png') }}" alt="IBEDC Logo" style="height: 60px;">
        </div>

        {{-- Session flash messages --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('customer.update.tenant.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @php $fields = session('update_fields', []); @endphp

            {{-- Full Name --}}
            @if (in_array('name', $fields))
                @if ($type === 'postpaid')
                    <div class="mb-3">
                        <label>Surname</label>
                        <input type="text" name="Surname" class="form-control"
                            value="{{ old('Surname', $customer->Surname) }}" required>
                    </div>
                    <div class="mb-3">
                        <label>First Name</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name', $customer->FirstName) }}" required>
                    </div>
                @else
                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name', $customer->OtherNames) }}" required>
                    </div>
                @endif
                <div class="mb-3">
                    <label>Upload Affidavit (for Name Change)</label>
                    <input type="file" name="name_document" class="form-control">
                </div>
            @endif

            {{-- Address --}}
            @if (in_array('address', $fields))
                @if ($type === 'postpaid')
                    <div class="mb-3">
                        <label>Address Line 1</label>
                        <input type="text" name="address1" class="form-control"
                            value="{{ old('address1', $customer->Address1) }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Address Line 2</label>
                        <input type="text" name="address2" class="form-control"
                            value="{{ old('address2', $customer->Address2) }}">
                    </div>
                @else
                    <div class="mb-3">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control"
                            value="{{ old('address', $customer->Address) }}" required>
                    </div>
                @endif
                <div class="mb-3">
                    <label>Upload Affidavit (for Address Change)</label>
                    <input type="file" name="address_document" class="form-control">
                </div>
            @endif

            {{-- Phone --}}
            @if (in_array('phone', $fields))
                <div class="mb-3">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control"
                        value="{{ old('phone', $customer->Phone ?? $customer->Mobile) }}">
                </div>
                <div class="mb-3">
                    <label>Alternate Phone Number</label>
                    <input type="text" name="alternate_phone" class="form-control" value="{{ old('alternate_phone') }}">
                </div>
                <div class="mb-3">
                    <label>Upload NIN (for Phone Update)</label>
                    <input type="file" name="phone_document" class="form-control">
                </div>
            @endif

            {{-- Email --}}
            @if (in_array('email', $fields))
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control"
                        value="{{ old('email', $customer->Email ?? ($customer->email ?? '')) }}">
                </div>
                <div class="mb-3">
                    <label>Upload NIN (optional for Email)</label>
                    <input type="file" name="email_document" class="form-control">
                </div>
            @endif

            {{-- Tenant NIN
            <div class="mb-3">
                <label>NIN</label>
                <input type="text" name="tenant_nin" class="form-control" value="{{ old('tenant_nin') }}" required>
            </div> --}}

            <div class="mb-3">
    <label>NIN</label>
    <div class="input-group">
        <input type="text" name="nin" id="nin" class="form-control" required>
        <button type="button" class="btn btn-primary" id="validateNinBtn">Validate</button>
    </div>
</div>
<div id="ninStatus" class="text-muted mt-1"></div>


            {{-- Type of Building --}}
            <div class="mb-3">
                <label>Type of Building</label>
                <select name="type_of_building" class="form-control" required>
                    <option value="">-- Select --</option>
                    <option value="Residential" {{ old('type_of_building') == 'Residential' ? 'selected' : '' }}>
                        Residential</option>
                    <option value="Commercial" {{ old('type_of_building') == 'Commercial' ? 'selected' : '' }}>Commercial
                    </option>
                    <option value="Industrial" {{ old('type_of_building') == 'Industrial' ? 'selected' : '' }}>Industrial
                    </option>
                </select>
            </div>

            {{-- Line of Business
            <div class="mb-3">
                <label>Type of Business</label>
                <input type="text" name="line_of_business" class="form-control" value="{{ old('line_of_business') }}">
            </div> --}}

            <div class="mb-3">
    <label for="business_type">Line of Business</label>
    <select name="business_type" class="form-control" required>
        <option value="">-- Select Line of Business --</option>
        <option>Artisan</option>
        <option>Hotel & Restaurant</option>
        <option>Welding & Fabrication</option>
        <option>Buying & Selling of goods</option>
        <option>Bakery</option>
        <option>Farming</option>
        <option>Ice block production</option>
        <option>Petty trader</option>
        <option>Supermarket</option>
        <option>Petrol & gas filling station</option>
        <option>Telecommunications</option>
        <option>Solar power</option>
        <option>Banking, finance & insurance related businesses</option>
        <option>IT related business</option>
        <option>MDAs- Federal Government</option>
        <option>MDAs- State Government</option>
        <option>MDAs- Local Government</option>
        <option>Other Manufacturing/ production</option>
        <option>Health related services- Hospital, clinic, pharmacy, mortuary etc</option>
        <option>School- tertiary, secondary, primary & nursery</option>
        <option>Transportation business- airline, shipping etc</option>
        <option>Vocational training</option>
        <option>Other services</option>
        <option>Oil & gas related services- Petrol/ gas filling station etc</option>
        <option>Water factory</option>
    </select>
</div>


            {{-- Know Landlord --}}
            <div class="mb-3">
                <label>Do you have info about your landlord?</label>
                <select name="knows_landlord" id="knows_landlord" class="form-control" required>
                    <option value="">-- Select --</option>
                    <option value="yes" {{ old('knows_landlord') == 'yes' ? 'selected' : '' }}>Yes</option>
                    <option value="no" {{ old('knows_landlord') == 'no' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            {{-- Landlord Info --}}
            <div id="landlord_fields" style="display: none;">
                <div class="mb-3">
                    <label>Landlord Name</label>
                    <input type="text" name="landlord_name" class="form-control" value="{{ old('landlord_name') }}">
                </div>
                <div class="mb-3">
                    <label>Landlord Phone</label>
                    <input type="text" name="landlord_phone" class="form-control"
                        value="{{ old('landlord_phone') }}">
                </div>
                <div class="mb-3">
                    <label>Landlord Address</label>
                    <input type="text" name="landlord_address" class="form-control"
                        value="{{ old('landlord_address') }}">
                </div>
            </div>

            {{-- State --}}
            <div class="mb-3">
                <label>State of Residence</label>
                <select name="state" class="form-control" required>
                    <option value="">-- Select State --</option>
                    @php
                        $states = ['Oyo', 'Kwara', 'Ogun', 'Osun', 'Kogi', 'Ekiti', 'Niger'];
                    @endphp
                    @foreach ($states as $state)
                        <option value="{{ $state }}" {{ old('state') == $state ? 'selected' : '' }}>
                            {{ $state }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Supporting Doc --}}
            <div class="mb-3">
                <label>General Supporting Document</label>
                <input type="file" name="supporting_doc" class="form-control" required>
            </div>

            <input type="hidden" name="occupancy_status" value="tenant">

            <button type="submit" class="btn btn-success">Submit KYC Update</button>
        </form>
    </div>

    {{-- JS --}}
    <script>
        const knowsLandlord = document.getElementById('knows_landlord');
        const landlordFields = document.getElementById('landlord_fields');

        knowsLandlord?.addEventListener('change', () => {
            landlordFields.style.display = knowsLandlord.value === 'yes' ? 'block' : 'none';
        });

        window.addEventListener('DOMContentLoaded', () => {
            if (knowsLandlord.value === 'yes') {
                landlordFields.style.display = 'block';
            }
        });
    </script>
@endsection
