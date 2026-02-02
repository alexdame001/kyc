@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">KYC Request Details</h3>

    <a href="{{ route('ccu.dashboard') }}" class="btn btn-secondary mb-3">← Back to Dashboard</a>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Customer Information</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Name:</strong> {{ $request->customer_name }}</li>
                <li class="list-group-item"><strong>Account Type:</strong> {{ ucfirst($request->account_type) }}</li>
                <li class="list-group-item"><strong>Identifier:</strong> {{ $request->identifier }}</li>
                <li class="list-group-item"><strong>NIN:</strong> {{ $request->nin ?? 'N/A' }}</li>
                <li class="list-group-item"><strong>Phone:</strong> {{ $request->phone }}</li>
                <li class="list-group-item"><strong>Email:</strong> {{ $request->email }}</li>
                <li class="list-group-item"><strong>Address:</strong> {{ $request->address }}</li>
                <li class="list-group-item"><strong>Occupancy:</strong> {{ ucfirst($request->occupancy_status) }}</li>
                <li class="list-group-item"><strong>Status:</strong> 
                    <span class="badge bg-{{ $request->status == 'Approved' ? 'success' : 'info' }}">{{ $request->status }}</span>
                </li>
                <li class="list-group-item"><strong>Last Updated:</strong> {{ $request->updated_at->format('d M Y, h:i A') }}</li>
            </ul>

            @if($request->document_path)
                <div class="mt-3">
                    <h6>Uploaded Document:</h6>
                    <a href="{{ asset('storage/' . $request->document_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View Document</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
