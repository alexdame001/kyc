@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h3 class="mb-4">{{ $validatorType }} Pending Validations</h3>

    <div class="card shadow">
        <div class="card-body">

            <div class="container-fluid">
    <h3 class="mb-4">{{ $validatorType }} Pending Validations</h3>

    <!-- Search bar -->
    <form method="GET" action="{{ route('firstlevel.list', $validatorType) }}" class="mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by Account ID, Name, State, or NIN" value="{{ $search }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="{{ route('firstlevel.list', $validatorType) }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Account Type</th>
                        <th>Account ID</th>
                        <th>Old Name</th>
                        <th>New Name</th>
                        <th>Old Address</th>
                        <th>New Address</th>
                        <th>Old Phone</th>
                        <th>New Phone</th>
                        <th>Old Email</th>
                        <th>New Email</th>
                        <th>NIN</th>
                        <th>State</th>
                        <th>Status</th>
                        <th>Document</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $index => $rec)
                        <tr>
                            <td>{{ ($page - 1) * $pageSize + $index + 1 }}</td>
                            <td>{{ $rec->account_type }}</td>
                            <td>{{ $rec->account_id }}</td>
                            <td>{{ $rec->old_fullname }}</td>
                            <td>{{ $rec->new_fullname }}</td>
                            <td>{{ $rec->old_address }}</td>
                            <td>{{ $rec->new_address }}</td>
                            <td>{{ $rec->old_phone }}</td>
                            <td>{{ $rec->new_phone }}</td>
                            <td>{{ $rec->old_email }}</td>
                            <td>{{ $rec->new_email }}</td>
                            <td>{{ $rec->nin }}</td>
                            <td>{{ $rec->state }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $rec->current_status }}</span>
                            </td>
                            <td>
                                @if($rec->document_path)
                                    <a href="{{ asset('storage/' . $rec->document_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center">No records found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="d-flex justify-content-between mt-3">
                <div>
                    Showing {{ ($page - 1) * $pageSize + 1 }} 
                    to {{ min($page * $pageSize, $totalCount) }} 
                    of {{ $totalCount }} entries
                </div>
                <div>
                    @if($page > 1)
                        <a href="{{ route('firstlevel.list', [$validatorType, 'page' => $page - 1]) }}" class="btn btn-outline-secondary btn-sm">Prev</a>
                    @endif
                    @if($page * $pageSize < $totalCount)
                        <a href="{{ route('firstlevel.list', [$validatorType, 'page' => $page + 1]) }}" class="btn btn-outline-secondary btn-sm">Next</a>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
