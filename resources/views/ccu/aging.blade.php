@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">CCU - KYC Aging Analysis</h2>

    <div class="mb-3">
        <a href="{{ route('ccu.aging.export') }}" class="btn btn-success">Download CSV</a>
        <a href="{{ route('ccu.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Account ID</th>
                    <th>Account Type</th>
                    <th>Full Name</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Total Age (Days)</th>
                    <th>Age Since Last Update</th>
                    <th>RICO Status</th>
                    <th>Billing Status</th>
                    <th>Audit Status</th>
                    <th>Admin Status</th>
                    <th>Current Stage</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agingData as $row)
                    @php
                        // Severity colors for total age days
                        if ($row->total_age_days > 10) {
                            $ageClass = 'table-danger';
                        } elseif ($row->total_age_days > 5) {
                            $ageClass = 'table-warning';
                        } else {
                            $ageClass = 'table-success';
                        }
                    @endphp
                    <tr>
                        <td>{{ $row->id }}</td>
                        <td>{{ $row->account_id }}</td>
                        <td>{{ $row->account_type }}</td>
                        <td>{{ $row->fullname }}</td>
                        <td>{{ $row->created_at }}</td>
                        <td>{{ $row->updated_at }}</td>
                        <td class="{{ $ageClass }}">{{ $row->total_age_days }}</td>
                        <td>{{ $row->age_since_last_update }}</td>
                        <td>{{ ucfirst($row->rico_status) }}</td>
                        <td>{{ ucfirst($row->billing_status) }}</td>
                        <td>{{ ucfirst($row->audit_status) }}</td>
                        <td>{{ ucfirst($row->admin_status) }}</td>
                        <td>{{ $row->current_stage }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center">No aging data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
