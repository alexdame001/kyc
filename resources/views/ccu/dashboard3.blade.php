<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CCU Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            background: #f8f9fa;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: none;
            position: relative;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        .card-body h3 {
            font-weight: 700;
        }

        .table th {
            background: #f1f3f5;
        }

        .changed {
            background: #d1e7dd;
            border-radius: 6px;
            padding: 3px 6px;
            color: #0f5132;
            font-weight: 600;
        }

        .no-change {
            color: #6c757d;
        }

        .search-form input,
        .search-form button {
            border-radius: 20px;
            padding: 10px 15px;
        }

        #toastExport {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 2000;
        }

        #spinnerOverlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 3000;
        }

        .dashboard-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 0;
        }

        .summary-title {
            font-size: 1.25rem;
            color: #6c757d;
            margin-top: 2rem;
        }

        .compare-box {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 10px;
        }

        .compare-header {
            background: #f1f1f1;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .preview-img {
            width: 100%;
            max-height: 300px;
            object-fit: contain;
            border-radius: 10px;
            margin-top: 10px;
        }

        .pagination {
            margin-top: 20px;
        }

        .page-link {
            border-radius: 50%;
            margin: 0 5px;
        }

        .stats-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.75rem;
            padding: 4px 8px;
        }
    </style>
</head>

<body>
<div class="container-fluid py-4">
    <div class="dashboard-header text-center">
        <h3 class="mb-0 fw-bold text-dark">CCU Dashboard</h3>
        <p class="text-muted mb-0">Total Records: {{ $kycForms->total() }} | Showing {{ $kycForms->firstItem() ?? 0 }} - {{ $kycForms->lastItem() ?? 0 }}</p>
    </div>

    <!-- Stage Cards -->
    <div class="row mb-4 text-center g-3">
        @php
            $stages = [
                'rkam' => ['label' => 'With RKAM', 'color' => 'warning', 'icon' => 'people-fill'],
                'bm' => ['label' => 'With BM', 'color' => 'info', 'icon' => 'briefcase-fill'],
                'rico' => ['label' => 'With RICO', 'color' => 'primary', 'icon' => 'person-badge-fill'],
                'billing' => ['label' => 'With Billing', 'color' => 'secondary', 'icon' => 'wallet2'],
            ];
        @endphp

        @foreach($stages as $key => $stage)
            <div class="col-md-3">
                <div class="card stage-card bg-{{ $stage['color'] }} text-white" data-stage="{{ $key }}">
                    <div class="card-body">
                        <h6>{{ $stage['label'] }}</h6>
                        <h3>{{ $stageCounts[strtoupper($key)]['pending'] ?? 0 }}</h3>
                        <i class="bi bi-{{ $stage['icon'] }} fs-3"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Totals -->
    <div class="row mb-4 text-center g-3">
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h6>Applications</h6>
                    <h3>{{ $totals['started'] ?? 0 }}</h3>
                    <i class="bi bi-list-check fs-3"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Completed</h6>
                    <h3>{{ $totals['approved'] ?? 0 }}</h3>
                    <i class="bi bi-check-circle-fill fs-3"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6>Rejected</h6>
                    <h3>{{ $totals['rejected'] ?? 0 }}</h3>
                    <i class="bi bi-x-circle-fill fs-3"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6>Pending</h6>
                    <h3>{{ $totals['pending'] ?? 0 }}</h3>
                    <i class="bi bi-clock fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mb-4">
        <h5 class="summary-title">KYC Applications</h5>
    </div>

    <!-- Search Form -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-8 mx-auto">
            <form method="GET" action="{{ route('ccu.dashboard') }}" class="search-form">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                        placeholder="🔍 Search by Account ID, Name, Phone, Email, NIN..." 
                        value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Search
                    </button>
                    @if(request('search'))
                        <a href="{{ route('ccu.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    @endif
                    <button type="button" id="exportBtn" class="btn btn-success">
                        <i class="bi bi-download"></i> Export All
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header fw-bold d-flex justify-content-between align-items-center">
            <span>KYC Applications</span>
            <span class="badge bg-primary">{{ $kycForms->total() }} Total</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle" id="kycTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Submission Date</th>
                            <th>Account Type</th>
                            <th>Account ID</th>
                            <th>Meter No</th>
                            <th>Name (Old / New)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kycForms as $index => $form)
                            <tr>
                                <td>{{ $kycForms->firstItem() + $index }}</td>
                                <td>{{ \Carbon\Carbon::parse($form->submitted_at)->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $form->account_type === 'prepaid' ? 'info' : 'secondary' }}">
                                        {{ ucfirst($form->account_type) }}
                                    </span>
                                </td>
                                <td><strong>{{ $form->account_id }}</strong></td>
                                <td>{{ $form->meter_no ?? '-' }}</td>
                                <td>
                                    <div><strong>Old:</strong> {{ $form->old_fullname ?? '-' }}</div>
                                    <div class="{{ $form->fullname !== $form->old_fullname ? 'changed' : 'no-change' }}">
                                        <strong>New:</strong> {{ $form->fullname ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $currentStatus = 'pending';
                                        $badgeClass = 'warning';
                                        
                                        if ($form->billing_status === 'approved') {
                                            $currentStatus = 'completed';
                                            $badgeClass = 'success';
                                        } elseif ($form->billing_status === 'rejected' || $form->rico_status === 'rejected' || $form->bm_status === 'rejected' || $form->rkam_status === 'rejected') {
                                            $currentStatus = 'rejected';
                                            $badgeClass = 'danger';
                                        } elseif ($form->billing_status === 'pending') {
                                            $currentStatus = 'billing';
                                            $badgeClass = 'secondary';
                                        } elseif ($form->rico_status === 'pending') {
                                            $currentStatus = 'rico';
                                            $badgeClass = 'primary';
                                        } elseif ($form->bm_status === 'pending') {
                                            $currentStatus = 'bm';
                                            $badgeClass = 'info';
                                        }
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }} text-uppercase">{{ $currentStatus }}</span>
                                </td>
                                <td>
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#infoModal" data-form='@json($form)'>
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-3">No KYC applications found</p>
                                    @if(request('search'))
                                        <a href="{{ route('ccu.dashboard') }}" class="btn btn-primary">Clear Search</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Showing {{ $kycForms->firstItem() ?? 0 }} to {{ $kycForms->lastItem() ?? 0 }} of {{ $kycForms->total() }} entries
                </div>
                <div>
                    {{ $kycForms->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast align-items-center text-bg-success border-0" id="toastExport" role="alert">
    <div class="d-flex">
        <div class="toast-body">
            <i class="bi bi-check-circle"></i> Export started! Your file will download shortly.
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
</div>

<!-- Spinner -->
<div id="spinnerOverlay">
    <div class="text-center">
        <div class="spinner-border text-success" style="width: 4rem; height: 4rem;" role="status"></div>
        <p class="mt-3 fw-bold">Exporting data...</p>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-file-text"></i> KYC Form Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                <div class="text-center text-muted">
                    <div class="spinner-border" role="status"></div>
                    <p>Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toast = new bootstrap.Toast(document.getElementById('toastExport'));
    const spinner = document.getElementById('spinnerOverlay');

    // Export CSV
    document.getElementById('exportBtn').addEventListener('click', function() {
        spinner.style.display = 'flex';
        toast.show();

        const searchParam = new URLSearchParams(window.location.search).get('search') || '';
        const exportUrl = '{{ route("ccu.export") }}' + (searchParam ? '?search=' + encodeURIComponent(searchParam) : '');

        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = exportUrl;
        document.body.appendChild(iframe);

        setTimeout(() => {
            spinner.style.display = 'none';
            document.body.removeChild(iframe);
        }, 2000);
    });

    // Stage Card Filtering
    document.querySelectorAll('.stage-card').forEach(card => {
        card.addEventListener('click', () => {
            const stage = card.dataset.stage;
            const url = new URL(window.location.href);
            url.searchParams.set('stage', stage);
            window.location.href = url.toString();
        });
    });

    // Modal Detail View
    document.getElementById('infoModal').addEventListener('show.bs.modal', function(event) {
        const form = JSON.parse(event.relatedTarget.getAttribute('data-form'));
        const createdAt = new Date(form.created_at).toLocaleString();
        const updatedAt = form.updated_at ? new Date(form.updated_at).toLocaleString() : '-';

        let html = `<div class="container-fluid">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light"><strong>Basic Information</strong></div>
                                    <div class="card-body">
                                        <p><strong>Account Type:</strong> <span class="badge bg-info">${form.account_type ?? '-'}</span></p>
                                        <p><strong>Account ID:</strong> ${form.account_id ?? '-'}</p>
                                        <p><strong>Meter No:</strong> ${form.meter_no ?? '-'}</p>
                                        <p><strong>NIN:</strong> ${form.nin ?? '-'}</p>
                                        <p><strong>State:</strong> ${form.state ?? '-'}</p>
                                        <p><strong>Submission Date:</strong> ${createdAt}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light"><strong>Approval Stages</strong></div>
                                    <div class="card-body">
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>RKAM:</strong> 
                                                <span class="badge bg-${form.rkam_status === 'approved' ? 'success' : form.rkam_status === 'rejected' ? 'danger' : 'warning'}">${form.rkam_status ?? '-'}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>BM:</strong> 
                                                <span class="badge bg-${form.bm_status === 'approved' ? 'success' : form.bm_status === 'rejected' ? 'danger' : 'warning'}">${form.bm_status ?? '-'}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>RICO:</strong> 
                                                <span class="badge bg-${form.rico_status === 'approved' ? 'success' : form.rico_status === 'rejected' ? 'danger' : 'warning'}">${form.rico_status ?? '-'}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>Billing:</strong> 
                                                <span class="badge bg-${form.billing_status === 'approved' ? 'success' : form.billing_status === 'rejected' ? 'danger' : 'warning'}">${form.billing_status ?? '-'}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h6 class="fw-bold mb-3 text-center">Old vs New Information Comparison</h6>
                        <div class="row text-center mb-4">
                            <div class="col-md-6">
                                <div class="compare-header mb-2">📋 Old Information</div>
                                <div class="compare-box text-start">
                                    <p><strong>Name:</strong> ${form.old_fullname ?? '-'}</p>
                                    <p><strong>Address:</strong> ${form.old_address ?? '-'}</p>
                                    <p><strong>Email:</strong> ${form.old_email ?? '-'}</p>
                                    <p><strong>Phone:</strong> ${form.old_phone ?? '-'}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="compare-header mb-2">✨ New Information</div>
                                <div class="compare-box text-start">
                                    <p><strong>Name:</strong> ${form.fullname ?? '-'}</p>
                                    <p><strong>Address:</strong> ${form.address ?? '-'}</p>
                                    <p><strong>Email:</strong> ${form.email ?? '-'}</p>
                                    <p><strong>Phone:</strong> ${form.phone ?? '-'}</p>
                                </div>
                            </div>
                        </div>`;

        if (form.document_path) {
            const docs = form.document_path.split(',');
            html += `<h6 class="fw-bold mb-3">📎 Attached Documents</h6><div class="row mb-3">`;
            docs.forEach((d,i) => {
                const url = '/storage/' + d.trim();
                const ext = d.split('.').pop().toLowerCase();
                const isImage = ['jpg','jpeg','png','gif','webp'].includes(ext);
                html += `<div class="col-md-4 mb-3">`;
                if(isImage){
                    html += `<a href="${url}" target="_blank"><img src="${url}" class="img-thumbnail" alt="Document ${i+1}"></a>`;
                } else {
                    html += `<a href="${url}" target="_blank" class="btn btn-outline-success w-100"><i class="bi bi-file-earmark-pdf"></i> View Document ${i+1}</a>`;
                }
                html += `</div>`;
            });
            html += `</div>`;
        } else {
            html += `<p class="text-muted text-center"><i class="bi bi-inbox"></i> No documents uploaded</p>`;
        }

        html += `<hr><div class="text-muted small text-center">
                    <p><strong>Last Updated:</strong> ${updatedAt}</p>
                 </div></div>`;

        document.getElementById('detailsContent').innerHTML = html;
    });
});
</script>
</body>
</html>
