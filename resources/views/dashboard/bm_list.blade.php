@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $title }}</h2>
            <p class="text-muted mb-0">
                Reviewed by: <strong>{{ Auth::user()->name }}</strong> 
                — Business Unit: <strong>{{ Auth::user()->location }}</strong>
            </p>
        </div>
        <a href="{{ route('bm.reports') }}" class="btn btn-info">← Back to Reports</a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Account/Meter No</th>
                        <th>Full Name</th>
                        <th>{{ $title === 'Rejected Accounts' ? 'Rejection Remarks' : 'Remarks' }}</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($forms as $form)
                        <tr>
                            <td><strong>{{ $form->account_id }}</strong></td>
                            <td>{{ $form->fullname ?? '-' }}</td>
                            <td>{{ $form->bm_remarks ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($form->submitted_at)->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                No {{ strtolower($title) }} found for your business unit.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection