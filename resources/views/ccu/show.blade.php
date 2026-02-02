@extends('layouts.app')

@section('content')
<div class="container">
    <h2>KYC Request Details</h2>

    <table class="table">
        <tr><th>Account ID:</th><td>{{ $kycForms->account_id }}</td></tr>
        <tr><th>Account Type:</th><td>{{ $kyc->account_type }}</td></tr>
        <tr><th>Name:</th><td>{{ $kyc->name }}</td></tr>
        <tr><th>Address:</th><td>{{ $kyc->address }}</td></tr>
        <tr><th>Status:</th><td>{{ $kyc->status }}</td></tr>
        <tr><th>Billing Status:</th><td>{{ $kyc->billing_status }}</td></tr>
        <tr><th>RICO Status:</th><td>{{ $kyc->rico_status }}</td></tr>
        <tr><th>Admin Status:</th><td>{{ $kyc->admin_status }}</td></tr>
        <tr><th>Submitted At:</th><td>{{ $kyc->created_at->format('d M Y H:i') }}</td></tr>
    </table>

    <a href="{{ route('ccu.index') }}" class="btn btn-secondary">Back to Dashboard</a>
</div>
@endsection
