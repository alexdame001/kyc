<!-- resources/views/dashboard/choose.blade.php -->
@extends('layouts.app')

@section('title', 'Choose Occupancy Type')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Tell us who you are</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <p>Please select your occupancy status to continue with the KYC update:</p>

    <div class="d-flex gap-3">
        <a href="{{ route('customer.update.landlord.form') }}" class="btn btn-success">I am a Landlord</a>
        {{-- <a href="{{ route('customer.update.tenant.form') }}" class="btn btn-primary">I am a Tenant</a> --}}
    </div>

    <div class="mt-4">
        <a href="{{ route('customer.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>
@endsection
