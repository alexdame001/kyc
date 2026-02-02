@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create New User</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Role</label>
            <select name="role" class="form-control" required>
                <option value="">-- Select Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}">{{ strtoupper($role) }}</option>
                @endforeach
            </select>
            @error('role') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

       <div class="mb-3">
    <label>Location</label>
    <select name="location_id" class="form-control" required>
        <option value="">-- Select Location --</option>
        @foreach($locations as $region => $locs)
            <optgroup label="{{ strtoupper($region) }}">
                @foreach($locs as $loc)
                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>

        <button type="submit" class="btn btn-primary">Create User</button>
    </form>
</div>
@endsection
