@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Review KYC Request</h3>

    <a href="{{ route('ccu.dashboard') }}" class="btn btn-secondary mb-3">← Back to Dashboard</a>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('ccu.review.submit', $request->id) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Customer Name</label>
                    <input type="text" class="form-control" value="{{ $request->customer_name }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">NIN</label>
                    <input type="text" class="form-control" value="{{ $request->nin }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" value="{{ $request->phone }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" value="{{ $request->address }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Occupancy Status</label>
                    <input type="text" class="form-control" value="{{ ucfirst($request->occupancy_status) }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Action</label>
                    <select name="ccu_status" class="form-select" required>
                        <option value="">-- Select Action --</option>
                        <option value="Approved">Approve</option>
                        <option value="Returned">Return for Correction</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks (if any)</label>
                    <textarea name="ccu_remarks" class="form-control" rows="3" placeholder="Enter any remarks..."></textarea>
                </div>

                <button type="submit" class="btn btn-success">Submit Review</button>
            </form>
        </div>
    </div>
</div>
@endsection
