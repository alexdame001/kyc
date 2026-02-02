@extends('layouts.app')

@section('content')

<div class="container">
<div class="d-flex justify-content-between align-items-center mb-4">
<h2>RKAM Reports</h2>
<a href="{{ route('rkam.dashboard') }}" class="btn btn-info">Back to Dashboard</a>
</div>

{{-- <div class="row">
    <div class="col-md-6">
        <div class="card text-center text-white bg-success mb-3">
            <div class="card-header">Total Approved</div>
            <div class="card-body">
                <h1 class="card-title">{{ $approvedCount }}</h1>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-center text-white bg-danger mb-3">
            <div class="card-header">Total Rejected</div>
            <div class="card-body">
                <h1 class="card-title">{{ $rejectedCount }}</h1>
            </div>
        </div>
    </div>
</div> --}}

<div class="row">
    <div class="col-md-6">
        <a href="{{ route('rkam.reports.approved') }}" class="text-decoration-none">
            <div class="card text-center text-white bg-success mb-3">
                <div class="card-header">Total Approved</div>
                <div class="card-body">
                    <h1 class="card-title">{{ $approvedCount }}</h1>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6">
        <a href="{{ route('rkam.reports.rejected') }}" class="text-decoration-none">
            <div class="card text-center text-white bg-danger mb-3">
                <div class="card-header">Total Rejected</div>
                <div class="card-body">
                    <h1 class="card-title">{{ $rejectedCount }}</h1>
                </div>
            </div>
        </a>
    </div>
</div>


</div>
@endsection