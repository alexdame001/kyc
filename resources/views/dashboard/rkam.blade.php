@extends('layouts.app')

@section('content')

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>RKAM Dashboard - Pending KYC Requests ({{ $staffState ?? 'All Locations' }})</h2>
        <a href="{{ route('rkam.reports') }}" class="btn btn-info">View Reports</a>
    </div>

    {{-- Flash messages --}}
    <div id="alertContainer"></div>

    <form id="bulkForm">
        @csrf
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>Account Type</th>
                    <th>Account ID</th>
                    <th>Name (Old)</th>
                    <th>Name (New)</th>
                    <th>Address (Old)</th>
                    <th>Address (New)</th>
                    <th>Email (Old)</th>
                    <th>Email (New)</th>
                    <th>Phone (Old)</th>
                    <th>Phone (New)</th>
                    <th>NIN</th>
                    <th>State</th>
                    <th>Status</th>
                    <th>Document</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($kycForms as $form)
                <tr id="row-{{ $form->id }}">
                    <td><input type="checkbox" class="checkItem" value="{{ $form->id }}"></td>
                    <td>{{ strtoupper($form->account_type) }}</td>
                    <td>{{ $form->account_id }}</td>
                    <td>{{ $form->old_fullname ?? '-' }}</td>
                    <td>{{ $form->new_fullname && $form->new_fullname !== $form->old_fullname ? $form->new_fullname : 'Not Updated' }}</td>
                    <td>{{ $form->old_address ?? '-' }}</td>
                    <td>{{ $form->new_address && $form->new_address !== $form->old_address ? $form->new_address : 'Not Updated' }}</td>
                    <td>{{ $form->old_email ?? '-' }}</td>
                    <td>{{ $form->new_email && $form->new_email !== $form->old_email ? $form->new_email : 'Not Updated' }}</td>
                    <td>{{ $form->old_phone ?? '-' }}</td>
                    <td>{{ $form->new_phone && $form->new_phone !== $form->old_phone ? $form->new_phone : 'Not Updated' }}</td>
                    <td>{{ $form->nin ?? '-' }}</td>
                    <td>{{ $form->state ?? '-' }}</td>
                    <td id="status-{{ $form->id }}">
                        @php
                            $status = $form->rkam_status ?? 'pending';
                            $badge = match($status){
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'warning'
                            };
                        @endphp
                        <span class="badge bg-{{ $badge }}">{{ ucfirst($status) }}</span>
                    </td>
                    <td>
                        @if($form->document_path)
                            @foreach(explode(',', $form->document_path) as $doc)
                                <a href="{{ asset('storage/' . trim($doc)) }}" target="_blank">View</a><br>
                            @endforeach
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-success btn-sm" onclick="singleAction({{ $form->id }}, 'approved')">Approve</button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="singleAction({{ $form->id }}, 'rejected')">Reject</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="16" class="text-center">No pending KYCs for {{ $staffState ?? 'your location' }}.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center mt-2">
            <div>
                <button type="button" class="btn btn-success" onclick="bulkAction('approved')">Bulk Approve</button>
                <button type="button" class="btn btn-danger" onclick="bulkAction('rejected')">Bulk Reject</button>
            </div>
            <div>
                Showing {{ $kycForms->count() }} of {{ $totalCount }} results (Page {{ $currentPage }})
            </div>
        </div>
    </form>

    {{-- Pagination --}}
    @if($totalCount > $pageSize)
        <nav aria-label="Pagination">
            <ul class="pagination justify-content-center mt-3">
                <li class="page-item">
                    <a class="page-link" href="?page={{ max(1, $currentPage - 1) }}&pageSize={{ $pageSize }}">Previous</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="?page={{ min(ceil($totalCount/$pageSize), $currentPage + 1) }}&pageSize={{ $pageSize }}">Next</a>
                </li>
            </ul>
        </nav>
    @endif
</div>

<script>
document.getElementById('selectAll').addEventListener('click', function() {
    document.querySelectorAll('.checkItem').forEach(cb => cb.checked = this.checked);
});

function handleResponse(response) {
    return response.json().catch(() => {
        alert('Invalid server response. You might be logged out.');
        throw new Error('Invalid JSON response');
    });
}

function singleAction(id, status) {
    let notes = '';
    if(status === 'rejected') {
        notes = prompt('Enter reason for rejection:') || '';
        if(!notes.trim()) return;
    }

    fetch(`/rkam/update/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({status, notes})
    })
    .then(handleResponse)
    .then(data => {
        if(data.success) {
            const badgeClass = status === 'approved' ? 'success' : 'danger';
            document.getElementById(`status-${id}`).innerHTML = `<span class="badge bg-${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message || 'Action failed', 'danger');
        }
    })
    .catch(err => console.error(err));
}

function bulkAction(status) {
    let selected = Array.from(document.querySelectorAll('.checkItem:checked')).map(cb => cb.value);
    if(!selected.length) return alert('Select at least one request.');

    let notes = '';
    if(status === 'rejected') {
        notes = prompt('Enter reason for rejection:') || '';
        if(!notes.trim()) return;
    }

    fetch(`/rkam/bulk-${status}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ids: selected, reason: notes})
    })
    .then(handleResponse)
    .then(data => {
        if(data.success) {
            selected.forEach(id => {
                const badgeClass = status === 'approved' ? 'success' : 'danger';
                document.getElementById(`status-${id}`).innerHTML = `<span class="badge bg-${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
            });
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message || 'Bulk action failed', 'danger');
        }
    })
    .catch(err => console.error(err));
}

function showAlert(message, type='success') {
    const alertContainer = document.getElementById('alertContainer');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    alertContainer.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 5000);
}
</script>

@endsection
