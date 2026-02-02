{{-- @extends('layouts.app')

@section('title', 'Staff Login')

@section('content')
<div class="container">
    <h2>Staff Login</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('staff.login.submit') }}">
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
@endsection --}}


@extends('layouts.app')

@section('content')
<style>
    /*
        This style block contains custom CSS to replicate the
        exact look and feel of the provided source code,
        including specific color codes and non-standard layouts
        that are not easily done with standard Tailwind classes.
    */
    *, *::before, *::after {
        box-sizing: border-box;
    }

    /* Keyframes for the background image animation */
    @keyframes background-animation {
        0% { background-image: url('https://financialcrimeacademy.org/wp-content/uploads/2023/03/3-65-1024x576.jpg'); }
        100% { background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ1fm8Qy7KzTGPawWF3WPPBlrQD47k1yIAGHxIOfFEdUViv6zk8aVBZiEI&s'); }
        /* 66.66% { background-image: url('data:image/jpeg;base64,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'); }
        100% { background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRXuEBBNe_7EEB6moPcYqgegdCHfm-QB5Nnb1rfjNMH0tWdtSNKfyVmd0c&s'); }
       */
      
        /* 40%{background-image: url('data:image/jpeg;base64,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')} */
    
    
    }

    
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        width: 100%;
        font-family: 'Inter', sans-serif;
    }

    body {
        /* Apply the background to the body for full-screen coverage */
        background-color: #f0f0f0; /* Fallback color */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        animation: background-animation 20s infinite ease-in-out;
    }

    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        width: 100%;
        /* The container now just centers the content over the body's background */
    }

    .login-card {
        background-color: rgba(255, 255, 255, 0.9);
        padding: 2rem 2.5rem;
        width: 100%;
        max-width: 450px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border-radius: 0.5rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .card-title {
        color: #000;
        font-weight: bold;
        text-align: center;
        margin-bottom: 0.5rem;
        font-size: 1.5rem;
    }
    
    .card-subtitle {
        color: #666;
        text-align: center;
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
        width: 100%;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #ccc;
        outline: none;
        border-radius: 4px;
        font-size: 1rem;
    }

    .btn-custom-orange {
        background-color: #ff9100;
        color: white;
        font-weight: bold;
        padding: 0.75rem 1.5rem;
        width: 100%;
        border-radius: 4px;
        transition: background-color 0.3s ease;
        border: none;
    }

    .btn-custom-orange:hover {
        background-color: #e68200;
    }
    
    .text-links {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin-top: 0.5rem;
    }
    
.text-links a {
        color: #ff9100;
        text-decoration: none;
        font-size: 0.875rem;
    }

    .text-links a:hover {
        text-decoration: underline;
    }
    
    .remember-me {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .remember-me input[type="checkbox"] {
        accent-color: #ff9100;
    }

    .copyright {
        position: absolute;
        bottom: 1.5rem;
        text-align: center;
        font-size: 0.8rem;
        color: white;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
    }
    
    @media (max-width: 640px) {
        .login-card {
            padding: 1.5rem;
        }
    }
</style>

<div class="login-container">
    <div class="login-card">
        <div class="text-center mb-6">
            <h2 class="card-title">Welcome !</h2>
            {{-- <p class="card-subtitle">Sign in to update your Personal Information</p> --}}
        </div>
        
        {{-- <img src="images/ibedc-logo.png" alt="IBEDC Logo" class="w-16 h-auto mb-4" /> --}}

        <!-- Login Form -->
        <form method="POST" action="{{ route('staff.login.submit') }}">
                @csrf
                
                <!-- Account Type Dropdown -->
                <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required autofocus>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
                
                <!-- Login Button -->
                <button type="submit" class="btn-custom-orange">
                    Log in
                </button>
            </form>
    </div>
    
    <div class="copyright">
        Copyright (c) 2025 IBEDC KYC 
    </div>
</div>
@endsection

