@extends('layouts.app')

@section('content')
<div class="container py-4">

    <!-- CORRECTED WELCOME MESSAGE - NOW USES $totalCount -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>BM Dashboard - Pending KYC Requests</h2>
            <div class="alert alert-info d-inline-block px-4 py-3 mb-0 border-0 shadow-sm rounded">
                <h5 class="mb-1">
                    <i class="fas fa-user me-2"></i>
                    Welcome back, <strong>{{ Auth::user()->name }}</strong>!
                </h5>
                <p class="mb-0 fs-5">
                    @if($totalCount == 0)
                        🎉 <strong>No pending KYC requests</strong> — you're all caught up!
                    @elseif($totalCount == 1)
                        You have <strong>1 pending KYC update</strong> to review.
                    @else
                        You have <strong>{{ $totalCount }} pending KYC updates</strong> to review.
                    @endif
                    <br>
                    <small class="text-muted">
                        Business Unit: <strong>{{ $staffState ?? 'All Locations' }}</strong>
                    </small>
                </p>
            </div>
        </div>
        <a href="{{ route('bm.reports') }}" class="btn btn-info btn-lg">View Reports</a>
    </div>

    {{-- Search by Account ID --}}
    <form method="GET" class="mb-4 d-flex gap-2">
        <input type="text" name="search" class="form-control w-25" placeholder="Search by Account ID..."
            value="{{ request('search') }}">
        <button type="submit" class="btn btn-primary">Search</button>
        @if(request('search'))
            <a href="{{ route('bm.dashboard') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>

    <form id="bulkForm">
        @csrf

        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th width="50">
                        <input type="checkbox" id="selectAllTop">
                    </th>
                    <th>Account Type</th>
                    <th>Account ID</th>
                    <th>Business Unit</th>
                    <th>Old Name</th>
                    <th>New Name</th>
                    <th>Old Address</th>
                    <th>New Address</th>
                    <th>Old Email</th>
                    <th>New Email</th>
                    <th>Old Phone</th>
                    <th>New Phone</th>
                    <th>NIN</th>
                    <th>Status</th>
                    <th>Documents</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($kycForms as $form)
                    <tr>
                        <td>
                            <input type="checkbox" name="ids[]" value="{{ $form->id }}" class="form-check-input checkItem">
                        </td>

                        <td>{{ strtoupper($form->account_type ?? 'N/A') }}</td>

                        <td><strong>{{ $form->account_id }}</strong></td>

                        <td>
                            <span class="badge bg-primary">{{ $form->buname ?? 'N/A' }}</span>
                        </td>

                        <td>{{ $form->old_fullname }}</td>
                        <td>
                            @if($form->new_fullname && $form->new_fullname !== $form->old_fullname)
                                <span class="text-success">{{ $form->new_fullname }}</span>
                            @else
                                <em>Not Updated</em>
                            @endif
                        </td>

                        <td>{{ $form->old_address }}</td>
                        <td>
                            @if($form->new_address && $form->new_address !== $form->old_address)
                                <span class="text-success">{{ $form->new_address }}</span>
                            @else
                                <em>Not Updated</em>
                            @endif
                        </td>

                        <td>{{ $form->old_email }}</td>
                        <td>
                            @if($form->new_email && $form->new_email !== $form->old_email)
                                <span class="text-success">{{ $form->new_email }}</span>
                            @else
                                <em>Not Updated</em>
                            @endif
                        </td>

                        <td>{{ $form->old_phone }}</td>
                        <td>
                            @if($form->new_phone && $form->new_phone !== $form->old_phone)
                                <span class="text-success">{{ $form->new_phone }}</span>
                            @else
                                <em>Not Updated</em>
                            @endif
                        </td>

                        <td>{{ $form->nin ?? 'N/A' }}</td>

                        <td>
                            <span class="badge bg-warning text-dark">
                                {{ ucfirst($form->bm_status ?? 'pending') }}
                            </span>
                        </td>

                        <td>
                            @if ($form->document_path)
                                @foreach (explode(',', $form->document_path) as $doc)
                                    <a href="{{ asset('storage/' . trim($doc)) }}" target="_blank" class="d-block mb-1">
                                        View Doc
                                    </a>
                                @endforeach
                            @else
                                <em>N/A</em>
                            @endif
                        </td>

                        <td>
                            <button type="button" class="btn btn-success btn-sm mb-1"
                                onclick="singleAction({{ $form->id }}, 'approved')">
                                Approve
                            </button>
                            <button type="button" class="btn btn-danger btn-sm"
                                onclick="singleAction({{ $form->id }}, 'rejected')">
                                Reject
                            </button>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="16" class="text-center py-4 text-muted">
                            <h5>No pending KYC requests for {{ $staffState ?? 'your location' }} at the moment.</h5>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center my-3">
            <div>
                <input type="checkbox" id="selectAllBottom"> <strong>Select All Visible</strong>
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-success" onclick="bulkApprove()">
                    Bulk Approve Selected
                </button>
                <button type="button" class="btn btn-danger" onclick="bulkReject()">
                    Bulk Reject Selected
                </button>
            </div>
        </div>
    </form>

    {{-- Pagination Info --}}
    <div class="d-flex justify-content-center mt-4">
        <p class="text-muted mb-0">
            Showing {{ ($currentPage - 1) * $pageSize + 1 }}
            to {{ min($currentPage * $pageSize, $totalCount) }}
            of <strong>{{ $totalCount }}</strong> pending request(s)
        </p>
    </div>
</div>

<script>
    // Select All Checkboxes
    document.getElementById('selectAllTop').onclick = function() {
        document.querySelectorAll('.checkItem').forEach(cb => cb.checked = this.checked);
    };
    document.getElementById('selectAllBottom').onclick = function() {
        document.querySelectorAll('.checkItem').forEach(cb => cb.checked = this.checked);
        document.getElementById('selectAllTop').checked = this.checked;
    };

    function handleResponse(response) {
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (err) {
                console.error('Invalid JSON:', text);
                alert('Session may have expired. Please log in again.');
                location.href = '/login';
                throw err;
            }
        });
    }

    function singleAction(id, status) {
        const notes = status === 'rejected' ? prompt('Enter rejection reason (required):') : prompt('Enter notes (optional):');
        if (status === 'rejected' && !notes?.trim()) {
            return alert('Reason is required for rejection.');
        }

        fetch(`/bm/update/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status, notes: notes || '' })
        })
        .then(handleResponse)
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Action failed');
        })
        .catch(err => console.error(err));
    }

    function bulkApprove() {
        const selected = Array.from(document.querySelectorAll('.checkItem:checked')).map(cb => cb.value);
        if (!selected.length) return alert('Please select at least one request.');

        if (confirm(`Approve ${selected.length} selected request(s)?`)) {
            fetch('/bm/bulk-approve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: selected })
            })
            .then(handleResponse)
            .then(data => {
                if (data.success) location.reload();
                else alert(data.message);
            });
        }
    }

    function bulkReject() {
        const selected = Array.from(document.querySelectorAll('.checkItem:checked')).map(cb => cb.value);
        if (!selected.length) return alert('Please select at least one request.');

        const reason = prompt(`Enter rejection reason for ${selected.length} request(s):`);
        if (!reason?.trim()) return alert('Reason is required for bulk rejection.');

        fetch('/bm/bulk-reject', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids: selected, reason })
        })
        .then(handleResponse)
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message);
        });
    }
</script>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endsection