@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h3 class="mb-4">First Level Validation Dashboard (RKAM & BM)</h3>

    <div class="row mb-4 text-center">
        <!-- RKAM -->
        <div class="col-md-6">
            <a href="{{ route('firstlevel.list', 'RKAM') }}" class="text-decoration-none">
                <div class="card text-white bg-info mb-3 shadow">
                    <div class="card-body">
                        <h5 class="card-title">RKAM Pending</h5>
                        <p class="card-text h2">{{ $rkamCount }}</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- BM -->
        <div class="col-md-6">
            <a href="{{ route('firstlevel.list', 'BM') }}" class="text-decoration-none">
                <div class="card text-white bg-warning mb-3 shadow">
                    <div class="card-body">
                        <h5 class="card-title">BM Pending</h5>
                        <p class="card-text h2">{{ $bmCount }}</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
