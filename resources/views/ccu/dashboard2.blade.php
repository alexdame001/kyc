@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">CCU Dashboard 2</h2>

    <!-- Stage Cards -->
    <div class="row mb-4">
        @foreach($stageCounts as $stage => $counts)
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3 stage-card" data-stage="{{ $stage }}">
                <div class="card-body">
                    <h5 class="card-title">{{ $stage }}</h5>
                    <p class="card-text">Pending: {{ $counts['pending'] }}</p>
                    <p class="card-text">Approved: {{ $counts['approved'] }}</p>
                    <p class="card-text">Rejected: {{ $counts['rejected'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- KYC Table -->
    <div class="card">
        <div class="card-header">
            <h5>KYC Applications @if($currentStageFilter) - {{ $currentStageFilter }} @endif</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Submission Date</th>
                        <th>Account Type</th>
                        <th>Account ID</th>
                        <th>Old Name</th>
                        <th>New Name</th>
                        <th>Old Address</th>
                        <th>New Address</th>
                        <th>Old Email</th>
                        <th>New Email</th>
                        <th>Old Phone</th>
                        <th>New Phone</th>
                        <th>Current Stage</th>
                        <th>RKAM Status</th>
                        <th>BM Status</th>
                        <th>RICO Status</th>
                        <th>Billing Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kycForms as $form)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($form->submitted_at)->format('Y-m-d H:i') }}</td>
                        <td>{{ $form->account_type }}</td>
                        <td>{{ $form->account_id }}</td>
                        <td>{{ $form->old_name }}</td>
                        <td>{{ $form->fullname ?? '-' }}</td>
                        <td>{{ $form->old_address }}</td>
                        <td>{{ $form->address ?? '-' }}</td>
                        <td>{{ $form->old_email }}</td>
                        <td>{{ $form->email ?? '-' }}</td>
                        <td>{{ $form->old_phone }}</td>
                        <td>{{ $form->phone ?? '-' }}</td>
                        <td>{{ $form->current_stage }}</td>
                        <td>{{ $form->rkam_status ?? '-' }}</td>
                        <td>{{ $form->bm_status ?? '-' }}</td>
                        <td>{{ $form->rico_status ?? '-' }}</td>
                        <td>{{ $form->billing_status ?? '-' }}</td>
                        <td>
                            <a href="{{ route('ccu.show', $form->id) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="17" class="text-center">No applications found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $kycForms->links() }}
            </div>
        </div>
    </div>
</div>

<!-- JS to make cards clickable -->
@section('scripts')
<script>
    document.querySelectorAll('.stage-card').forEach(card => {
        card.addEventListener('click', () => {
            const stage = card.dataset.stage;
            const url = new URL(window.location.href);
            url.searchParams.set('stage', stage);
            window.location.href = url.toString();
        });
    });
</script>
@endsection

@endsection
