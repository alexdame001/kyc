@extends('layouts.app')

@section('title', ucfirst($role) . ' Login')

@section('content')
<div class="container">
    <h2>{{ strtoupper($role) }} Login</h2>

    @if ($errors->has('login'))
        <div class="alert alert-danger">{{ $errors->first('login') }}</div>
    @endif

    <form method="POST" action="{{ route('role.login', $role) }}">
        @csrf

        <div class="mb-3">
            <label for="Email">Username</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
@endsection
