<!-- resources/views/dashboard/audit.blade.php -->
@extends('layouts.app')

@section('title', 'Audit Dashboard')

@section('content')
<div class="container">

  <div style="position: absolute; top: 0; right: 0;">
            <img src="{{ asset('images/ibedc-logo.png') }}" alt="IBEDC Logo" style="height: 60px;">
        </div>
    <h1>Audit Dashboard</h1>

    <h3>Billing-Approved KYC Forms</h3>

    @if($kycForms->isEmpty())
        <p>No forms ready for auditing.</p>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Account ID</th>
                    <th>Name</th>
                    <th>Submitted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kycForms as $form)
                <tr>
                    <td>{{ $form->id }}</td>
                    <td>{{ $form->account_id }}</td>
                    <td>{{ $form->fullname }}</td>
                    <td>{{ $form->submitted_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <form method="POST" action="{{ route('audit.approve', $form->id) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $form->id }}">Reject</button>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal{{ $form->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $form->id }}" aria-hidden="true">
                          <div class="modal-dialog">
                            <form method="POST" action="{{ route('audit.reject', $form->id) }}">
                                @csrf
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="rejectModalLabel{{ $form->id }}">Reject KYC Form #{{ $form->id }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="remarks" class="form-label">Reason for Rejection</label>
                                        <textarea name="remarks" class="form-control" required></textarea>
                                    </div>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger">Submit Rejection</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                  </div>
                                </div>
                            </form>
                          </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
