@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $title }}</h2>
        <a href="{{ route('rico.reports') }}" class="btn btn-info">Back to Reports</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Account/Meter No</th>
                <th>Full Name</th>
                <th>Remarks</th>
                <th>Submitted At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($forms as $form)
                <tr>
                    <td>{{ $form->account_id }}</td>
                    <td>{{ $form->fullname ?? '-' }}</td>
                    <td>{{ $form->rkam_remarks ?? '-' }}</td>
                    <td>{{ $form->submitted_at }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
