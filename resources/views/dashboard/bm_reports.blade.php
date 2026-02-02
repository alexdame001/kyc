@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">KYC Review Report</h3>
                            <p class="mb-0 fs-5 opacity-90">
                                <strong>{{ Auth::user()->name }}</strong> 
                                — Business Unit: <strong>{{ $staffLocation }}</strong>
                            </p>
                        </div>
                        <a href="{{ route('bm.dashboard') }}" class="btn btn-light btn-lg">
                            ← Back to Dashboard
                        </a>
                    </div>
                </div>

                <div class="card-body py-5">
                    <div class="row text-center g-4">
                        <!-- Approved -->
                        <div class="col-md-6">
                            <a href="{{ route('bm.reports.approved') }}" class="text-decoration-none">
                                <div class="card h-100 bg-success text-white border-0 shadow-sm hover-shadow-lg transition">
                                    <div class="card-body py-5">
                                        <i class="fas fa-check-circle fa-3x mb-3 opacity-75"></i>
                                        <h2 class="card-title mb-2">{{ $approvedCount }}</h2>
                                        <p class="fs-5 mb-0">Total Approved</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Rejected -->
                        <div class="col-md-6">
                            <a href="{{ route('bm.reports.rejected') }}" class="text-decoration-none">
                                <div class="card h-100 bg-danger text-white border-0 shadow-sm hover-shadow-lg transition">
                                    <div class="card-body py-5">
                                        <i class="fas fa-times-circle fa-3x mb-3 opacity-75"></i>
                                        <h2 class="card-title mb-2">{{ $rejectedCount }}</h2>
                                        <p class="fs-5 mb-0">Total Rejected</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Pending (Bonus) -->
                        <div class="col-md-6">
                            <div class="card h-100 bg-warning text-dark border-0 shadow-sm">
                                <div class="card-body py-5">
                                    <i class="fas fa-clock fa-3x mb-3 opacity-75"></i>
                                    <h2 class="card-title mb-2">{{ $pendingCount ?? 0 }}</h2>
                                    <p class="fs-5 mb-0">Pending Review</p>
                                </div>
                            </div>
                        </div>

                        <!-- Total Handled -->
                        <div class="col-md-6">
                            <div class="card h-100 bg-info text-white border-0 shadow-sm">
                                <div class="card-body py-5">
                                    <i class="fas fa-tasks fa-3x mb-3 opacity-75"></i>
                                    <h2 class="card-title mb-2">{{ $totalHandled ?? 0 }}</h2>
                                    <p class="fs-5 mb-0">Total Handled</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-center text-muted py-3">
                    <small>
                        This report includes only customers assigned to your business unit ({{ $staffLocation }}).
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .transition {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .hover-shadow-lg:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
    }
</style>

<!-- Font Awesome for icons (if not already in layouts.app) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endsection