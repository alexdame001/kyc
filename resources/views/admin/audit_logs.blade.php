@extends('layouts.app')

@section('title', 'Audit Log Dashboard')

@section('content')

<div class="container">
<h2 class="my-4">Audit Log Dashboard</h2>
<div class="card mb-4">
<div class="card-header">Filter Logs</div>
<div class="card-body">
<form action="{{ route('admin.audit.logs') }}" method="GET" class="row g-3">
<div class="col-md-4">
<label for="account_id" class="form-label">Account ID</label>
<input type="text" class="form-control" id="account_id" name="account_id" value="{{ old('account_id', request('account_id')) }}" placeholder="Enter Account ID">
</div>
<div class="col-md-4">
<label for="start_date" class="form-label">Start Date</label>
<input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', request('start_date')) }}">
</div>
<div class="col-md-4">
<label for="end_date" class="form-label">End Date</label>
<input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', request('end_date')) }}">
</div>
<div class="col-12 d-flex align-items-end mt-3">
<button type="submit" class="btn btn-primary me-2">Filter</button>
<a href="{{ route('admin.audit.logs') }}" class="btn btn-secondary">Clear Filter</a>
</div>
</form>
</div>
</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Time</th>
            <th>Age</th>
            <th>User ID</th>
            <th>Action</th>
            <th>Description</th>
            <th>IP Address</th>
            <th>User Agent</th>
            <th>Old Values</th>
            <th>New Values</th>
        </tr>
    </thead>
    <tbody>
        @foreach($logs as $log)
        <tr>
            <td>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}</td>
            <td>{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</td>
            <td>{{ $log->user_id }}</td>
            <td>{{ $log->action }}</td>
            <td>{{ $log->description }}</td>
            <td>{{ $log->ip_address }}</td>
            <td>{{ $log->user_agent }}</td>
            <td>
                @if($log->old_values)
                    @php $old_values = json_decode($log->old_values, true); @endphp
                    @if (is_array($old_values))
                        <ul>
                            @foreach($old_values as $key => $value)
                                <li><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    N/A
                @endif
            </td>
            <td>
                @if($log->new_values)
                    @php $new_values = json_decode($log->new_values, true); @endphp
                    @if (is_array($new_values))
                        <ul>
                            @foreach($new_values as $key => $value)
                                <li><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    N/A
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="d-flex justify-content-center">
    {{ $logs->links() }}
</div>

</div>
@endsection