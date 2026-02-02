<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Staff Profile</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 400px; margin: auto; margin-top: 50px; }
        input, select, button { width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #ccc; }
        button { background: #007bff; color: #fff; border: none; cursor: pointer; }
        button:hover { background: #0069d9; }
        .error { color: red; margin-bottom: 10px; }
        .success { color: green; margin-bottom: 10px; }
    </style>
</head>
<body>

<h2 style="text-align:center;">Edit Staff Profile</h2>

@if(session('success'))
    <div class="success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="error">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ url('/staff/profile/'.$user->id) }}">
    @csrf
    @method('PUT')

    <input type="text" name="name" placeholder="Full Name" value="{{ old('name', $user->name) }}" required>

    <input type="email" placeholder="Email" value="{{ $user->email }}" disabled>

    <select name="role" required>
        <option value="">-- Select Role --</option>
        @foreach($roles as $role)
            <option value="{{ $role }}" {{ $user->role == $role ? 'selected' : '' }}>{{ strtoupper($role) }}</option>
        @endforeach
    </select>

    <input type="text" name="location" placeholder="Location / Region" value="{{ old('location', $user->location) }}" required>

    <button type="submit">Update Profile</button>
</form>

</body>
</html>
