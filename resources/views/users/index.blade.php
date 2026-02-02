@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Users</h3>
    <a href="{{ route('users.create') }}" class="btn btn-primary mb-3">+ New User</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Location</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ strtoupper($user->role) }}</td>
                    <td>{{ $user->location?->name ?? '-' }}</td>
                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $users->links() }}
</div>
@endsection
