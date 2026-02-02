<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
</form>

@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Login</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ url('/login') }}">
        @csrf

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required autofocus>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
@endsection
