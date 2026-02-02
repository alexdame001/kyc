@extends('layouts.app')

@section('title', 'CCU Dashboard')

{{-- Perfect Smart Age Formatter - Hours → Days → Months --}}
@php
function formatAge($submitted_at) {
    $submitted = \Carbon\Carbon::parse($submitted_at);
    $now = \Carbon\Carbon::now();

    if ($submitted->greaterThan($now)) {
        return '0 hrs'; // future dates = 0 hrs
    }

    $hours = $now->diffInHours($submitted);

    if ($hours < 24) {
        return $hours . ' hr' . ($hours == 1 ? '' : 's');
    }

    // Crossed into next month → show months
    if ($now->year > $submitted->year || $now->month > $submitted->month) {
        $months = $now->diffInMonths($submitted);
        return $months . ' month' . ($months == 1 ? '' : 's');
    }

    $days = $now->diffInDays($submitted);
    return $days . ' day' . ($days == 1 ? '' : 's');
}
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark">CCU Dashboard</h3>
        <p class="text-muted">Real-time KYC Overview • Updated every 2 minutes</p>
    </div>

    <!-- Clickable Summary Cards -->
    <div class="row mb-4 g-3 text-center">
        <div class="col-md-3 col-6">
            <a href="{{ route('ccu.dashboard', ['filter' => 'rkam']) }}" class="text-decoration-none">
                <div class="card bg-warning text-dark shadow-sm h-100">
                    <div class="card-body py-4">
                        <h3 class="mb-0">{{ $counts['with_rkam'] }}</h3>
                        <p class="mb-0 fw-bold">With RKAM</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('ccu.dashboard', ['filter' => 'bm']) }}" class="text-decoration-none">
                <div class="card bg-info text-white shadow-sm h-100">
                    <div class="card-body py-4">
                        <h3 class="mb-0">{{ $counts['with_bm'] }}</h3>
                        <p class="mb-0 fw-bold">With BM</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('ccu.dashboard', ['filter' => 'rico']) }}" class="text-decoration-none">
                <div class="card bg-primary text-white shadow-sm h-100">
                    <div class="card-body py-4">
                        <h3 class="mb-0">{{ $counts['with_rico'] }}</h3>
                        <p class="mb-0 fw-bold">With RICO</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('ccu.dashboard', ['filter' => 'billing']) }}" class="text-decoration-none">
                <div class="card bg-secondary text-white shadow-sm h-100">
                    <div class="card-body py-4">
                        <h3 class="mb-0">{{ $counts['with_billing'] }}</h3>
                        <p class="mb-0 fw-bold">With Billing</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row mb-4 g-3 text-center">
        <div class="col-md-3 col-6">
            <a href="{{ route('ccu.dashboard') }}" class="text-decoration-none">
                <div class="card bg-dark text-white shadow-sm h-100">
                    <div class="card-body py-4">
                        <h3 class="mb-0">{{ $counts['total_applications'] }}</h3>
                        <p class="mb-0 fw-bold">Total Applications</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('ccu.dashboard', ['filter' => 'pending']) }}" class="text-decoration-none">
                <div class="card bg-orange text-white shadow-sm h-100">
                    <div class="card-body py-4">
                        <h3 class="mb-0">{{ $counts['total_pending'] }}</h3>
                        <p class="mb-0 fw-bold">Total Pending</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('ccu.dashboard', ['filter' => 'approved']) }}" class="text-decoration-none">
                <div class="card bg-success text-white shadow-sm h-100">
                    <div class="card-body py-4">
                        <h3 class="mb-0">{{ $counts['total_approved'] }}</h3>
                        <p class="mb-0 fw-bold">Approved</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('ccu.dashboard', ['filter' => 'rejected']) }}" class="text-decoration-none">
                <div class="card bg-danger text-white shadow-sm h-100">
                    <div class="card-body py-4">
                        <h3 class="mb-0">{{ $counts['total_rejected'] }}</h3>
                        <p class="mb-0 fw-bold">Rejected</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Search -->
    <div class="text-center mb-4">
        <form method="GET" class="d-inline-block">
            <div class="input-group" style="max-width: 600px;">
                <input type="text" name="search" class="form-control" placeholder="Search Account ID, Name, Phone, NIN..." value="{{ request('search') }}">
                <button class="btn btn-primary">Search</button>
                @if(request()->filled('search') || request()->filled('filter'))
                    <a href="{{ route('ccu.dashboard') }}" class="btn btn-outline-secondary ms-2">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Applications Table -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <h5 class="mb-0">KYC Applications ({{ $forms->total() }})</h5>
            <div>
                <a href="{{ route('ccu.exportCsvPage', request()->query()) }}" class="btn btn-sm btn-success me-2">Download Current Page CSV</a>
                <a href="{{ route('ccu.exportCsvAll', request()->query()) }}" class="btn btn-sm btn-primary">Download All CSV</a>
                <a href="{{ route('ccu.dashboard') }}?refresh=1" class="btn btn-light btn-sm">Refresh Counts</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Account ID</th>
                            <th>Name (Old → New)</th>
                            <th>Type</th>
                            <th>Submitted</th>
                            <th>Stage</th>
                            <th>Responsible Staff</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($forms as $f)
                            @php
                                $ageText = formatAge($f->submitted_at);
                                $isOverdue = Str::contains($ageText, 'month') || (Str::contains($ageText, 'day') && (int)filter_var($ageText, FILTER_SANITIZE_NUMBER_INT) > 7);
                                $isWarning = Str::contains($ageText, 'day') && (int)filter_var($ageText, FILTER_SANITIZE_NUMBER_INT) > 3 && (int)filter_var($ageText, FILTER_SANITIZE_NUMBER_INT) <= 7;
                            @endphp
                            <tr class="{{ $isOverdue ? 'table-danger' : ($isWarning ? 'table-warning' : '') }}">
                                <td><strong>{{ $f->account_id }}</strong></td>
                                <td>
                                    <div><small class="text-muted">Old:</small> {{ $f->old_fullname }}</div>
                                    <div class="{{ $f->fullname != $f->old_fullname ? 'text-success fw-bold' : 'text-muted' }}">
                                        New: {{ $f->fullname }}
                                    </div>
                                </td>
                                <td>{{ ucfirst($f->account_type) }}</td>
                                <td>{{ \Carbon\Carbon::parse($f->submitted_at)->format('d M Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-{{ $f->current_stage == 'Approved' ? 'success' : (str_ends_with($f->current_stage, 'Review') ? 'warning' : 'danger') }}">
                                        {{ $f->current_stage }}
                                    </span>
                                </td>
                                <td>{{ $f->responsible_email ?? '-' }}</td>
                                <td>
                                    <button class="btn btn-outline-primary btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#infoModal" 
                                            data-form='@json($f)'>
                                        <i class="bi bi-eye"></i> View more
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1"></i><br>No records found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light text-center">
                {{ $forms->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal - Full Old vs New + Documents -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-file-person"></i> KYC Application Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3">Loading details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-orange { background-color: #fd7e14 !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('infoModal');
    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const form = JSON.parse(button.getAttribute('data-form'));
        const submitted = new Date(form.submitted_at).toLocaleString();

        let html = `
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-primary">
                        <div class="card-header bg-light"><strong>Old Information (From System)</strong></div>
                        <div class="card-body">
                            <p><strong>Name:</strong> ${form.old_fullname}</p>
                            <p><strong>Address:</strong> ${form.old_address}</p>
                            <p><strong>Phone:</strong> ${form.old_phone}</p>
                            <p><strong>Email:</strong> ${form.old_email}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-success">
                        <div class="card-header bg-light"><strong>New Information (Customer Update)</strong></div>
                        <div class="card-body">
                            <p><strong>Name:</strong> <span class="text-success fw-bold">${form.fullname}</span></p>
                            <p><strong>Address:</strong> ${form.address || '-'}</p>
                            <p><strong>Phone:</strong> ${form.phone || '-'}</p>
                            <p><strong>Email:</strong> ${form.email || '-'}</p>
                            <p><strong>NIN:</strong> ${form.nin || '-'}</p>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <div class="col-md-6">
                    <h6>Approval Status</h6>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            RKAM <span class="badge bg-${form.rkam_status === 'approved' ? 'success' : (form.rkam_status === 'rejected' ? 'danger' : 'warning')}">${form.rkam_status || 'Pending'}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            BM <span class="badge bg-${form.bm_status === 'approved' ? 'success' : (form.bm_status === 'rejected' ? 'danger' : 'warning')}">${form.bm_status || 'Pending'}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            RICO <span class="badge bg-${form.rico_status === 'approved' ? 'success' : (form.rico_status === 'rejected' ? 'danger' : 'warning')}">${form.rico_status || 'Pending'}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Billing <span class="badge bg-${form.billing_status === 'approved' ? 'success' : (form.billing_status === 'rejected' ? 'danger' : 'warning')}">${form.billing_status || 'Pending'}</span>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <p><strong>Account ID:</strong> ${form.account_id}</p>
                    <p><strong>Type:</strong> ${form.account_type.toUpperCase()}</p>
                    <p><strong>Submitted:</strong> ${submitted}</p>
                    <p><strong>Age:</strong> ${formatSmartAgeInModal(form.submitted_at)}</p>
                    <p><strong>Responsible Staff:</strong> ${form.responsible_email || '-'}</p>
                </div>
            </div>`;

        if (form.document_path) {
            const docs = form.document_path.split(',');
            html += `<hr><h6>Attached Documents</h6><div class="row g-3">`;
            docs.forEach(d => {
                const url = '/storage/' + d.trim();
                const ext = d.split('.').pop().toLowerCase();
                const isImage = ['jpg','jpeg','png','gif','webp'].includes(ext);
                html += `<div class="col-md-4 text-center">
                    ${isImage 
                        ? `<a href="${url}" target="_blank"><img src="${url}" class="img-thumbnail" style="height:180px; object-fit:cover;"></a>`
                        : `<a href="${url}" target="_blank" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-file-earmark-pdf"></i> View Document</a>`
                    }
                </div>`;
            });
            html += `</div>`;
        }

        document.getElementById('detailsContent').innerHTML = html;
    });
});

// Reusable age formatter for modal
function formatSmartAgeInModal(submitted_at) {
    const submitted = new Date(submitted_at);
    const now = new Date();
    if (submitted > now) return '0 hrs';

    const hours = Math.floor((now - submitted) / (1000 * 60 * 60));

    if (hours < 24) return hours + ' hr' + (hours === 1 ? '' : 's');

    // Crossed into next month?
    if (now.getFullYear() > submitted.getFullYear() || now.getMonth() > submitted.getMonth()) {
        let months = (now.getFullYear() - submitted.getFullYear()) * 12 + (now.getMonth() - submitted.getMonth());
        if (now.getDate() < submitted.getDate()) months--;
        return months + ' month' + (months === 1 ? '' : 's');
    }

    const days = Math.floor(hours / 24);
    return days + ' day' + (days === 1 ? '' : 's');
}
</script>
@endsection
