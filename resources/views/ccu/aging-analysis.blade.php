@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">KYC Aging Analysis</h2>

    <div class="mb-3">
        <a href="{{ route('ccu.aging.csv') }}" class="btn btn-success">Download CSV</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
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
            @foreach($agingData as $data)
                <tr 
                    @if($data->total_age_days > 10)
                        style="background-color: #f8d7da;"
                    @elseif($data->total_age_days > 5)
                        style="background-color: #fff3cd;"
                    @else
                        style="background-color: #d4edda;"
                    @endif
                >
                    <td>{{ $data->id }}</td>
                    <td>{{ $data->account_id }}</td>
                    <td>{{ $data->account_type }}</td>
                    <td>{{ $data->fullname }}</td>
                    <td>{{ $data->created_at }}</td>
                    <td>{{ $data->updated_at }}</td>
                    <td>{{ $data->total_age_days }}</td>
                    <td>{{ $data->age_since_last_update }}</td>
                    <td>{{ $data->rico_status }}</td>
                    <td>{{ $data->billing_status }}</td>
                    <td>{{ $data->audit_status }}</td>
                    <td>{{ $data->admin_status }}</td>
                    <td>{{ $data->current_stage }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
