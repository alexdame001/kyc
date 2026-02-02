<!-- resources/views/dashboard/admin.blade.php -->
@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container">

    <div style="position: absolute; top: 0; right: 0;">
            <img src="{{ asset('images/ibedc-logo.png') }}" alt="IBEDC Logo" style="height: 60px;">
        </div>
    <h1>Admin Dashboard</h1>

    <h3>All KYC Submissions</h3>

    @if($kycForms->isEmpty())
        <p>No submissions yet.</p>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Account ID</th>
                    <th>Name</th>
                    <th>Submitted By</th>
                    <th>Submitted At</th>
                    <th>RICO</th>
                    <th>Billing</th>
                    <th>Audit</th>
                    <th>Document</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kycForms as $form)
                <tr>
                    <td>{{ $form->id }}</td>
                    <td>{{ $form->account_id }}</td>
                    <td>{{ $form->fullname }}</td>
                    <td>{{ $form->submitted_by }}</td>
                    <td>{{ $form->submitted_at->format('Y-m-d H:i') }}</td>
                    <td>{{ ucfirst($form->rico_status) }}</td>
                    <td>{{ ucfirst($form->billing_status) }}</td>
                    <td>{{ ucfirst($form->audit_status) }}</td>
                    <td>
                        @if ($form->document_path)
                            <a href="{{ asset('storage/' . $form->document_path) }}" target="_blank">View</a>
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
