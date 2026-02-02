@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-10 p-6 bg-white shadow-lg rounded-lg">
    <h2 class="text-2xl font-semibold text-center mb-6">🔒 Change Password</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('staff.change-password.update', ['id' => $staff->id]) }}" method="POST">
        @csrf
        @method('POST') <!-- or 'PUT' if your route expects PUT -->

        <div class="mb-4">
            <label for="current_password" class="block text-gray-700 font-medium mb-2">Current Password</label>
            <input type="password" name="current_password" id="current_password" 
                   class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" 
                   required>
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 font-medium mb-2">New Password</label>
            <input type="password" name="password" id="password" 
                   class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" 
                   required>
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-gray-700 font-medium mb-2">Confirm New Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" 
                   class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" 
                   required>
        </div>

        <button type="submit" 
                class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition duration-300">
            Update Password
        </button>
    </form>
</div>
@endsection
