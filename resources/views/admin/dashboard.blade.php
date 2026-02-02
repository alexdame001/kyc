@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Welcome, Admin</h2>

    {{-- Analytics Summary --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5>Total KYC Forms</h5>
                    <h3>{{ $kycStats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5>Approved</h5>
                    <h3>{{ $kycStats['approved'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5>Pending</h5>
                    <h3>{{ $kycStats['pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5>Rejected</h5>
                    <h3>{{ $kycStats['rejected'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- User Management --}}
    <div class="mb-4">
        <h4>User Management</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ ucfirst($user->role) }}</td>
                    <td>
                        @if ($user->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.toggleUser', $user->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm {{ $user->is_active ? 'btn-danger' : 'btn-success' }}">
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- KYC Review --}}
    <div>
        <h4>KYC Submissions</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Date Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kycForms as $form)
                <tr>
                    <td>{{ $form->customer_name }}</td>
                    <td>{{ ucfirst($form->status) }}</td>
                    <td>{{ $form->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.kyc.review', $form->id) }}" class="btn btn-sm btn-primary">Review</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
