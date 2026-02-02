@extends('layouts.app')

@section('content')
<div class="container my-4">
    <h1 class="mb-4">📝 Staff Profiling</h1>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Staff Creation Form --}}
    <div class="card mb-4">
        <div class="card-header">Add New Staff</div>
        <div class="card-body">
            <form action="{{ route('staff.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                    </div>

                     <div class="col-md-4 mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="phone" name="phone" id="phone" class="form-control" value="{{ old('phone') }}" required>
                    </div>

                    {{-- <div class="col-md-4 mb-3">
                        <label for="location" class="form-label">Region/Location</label>
                        <input type="text" name="location" id="location" class="form-control" value="{{ old('location') }}" required>
                    </div> --}}


                    <div class="col-md-4 mb-3">
    <label for="location" class="form-label">Region/Location</label>
    <select name="location" id="location" class="form-control" required>
        <option value="">Select Location</option>

        {{-- HQ & Other Offices --}}
        <option value="HEADQUARTER" {{ old('location') == 'HEADQUARTER' ? 'selected' : '' }}>HEADQUARTER</option>
        <option value="APATA" {{ old('location') == 'APATA' ? 'selected' : '' }}>APATA</option>
         <option value="IBADAN" {{ old('location') == 'IBADAN' ? 'selected' : '' }}>IBADAN</option>
        <option value="Akanran" {{ old('location') == 'Akanran' ? 'selected' : '' }}>Akanran</option>
        <option value="BABOKO" {{ old('location') == 'BABOKO' ? 'selected' : '' }}>BABOKO</option>
        <option value="CHALLENGE" {{ old('location') == 'CHALLENGE' ? 'selected' : '' }}>CHALLENGE</option>
        <option value="Dugbe" {{ old('location') == 'Dugbe' ? 'selected' : '' }}>Dugbe</option>
        <option value="Ede" {{ old('location') == 'Ede' ? 'selected' : '' }}>Ede</option>
        <option value="Ijebu Ode" {{ old('location') == 'Ijebu Ode' ? 'selected' : '' }}>Ijebu Ode</option>
        <option value="IJEUN" {{ old('location') == 'IJEUN' ? 'selected' : '' }}>IJEUN</option>
        <option value="Ikirun" {{ old('location') == 'Ikirun' ? 'selected' : '' }}>Ikirun</option>
        <option value="ILE-IFE" {{ old('location') == 'ILE-IFE' ? 'selected' : '' }}>ILE-IFE</option>
        <option value="Ilesa" {{ old('location') == 'Ilesa' ? 'selected' : '' }}>Ilesa</option>
        <option value="JEBBA" {{ old('location') == 'JEBBA' ? 'selected' : '' }}>JEBBA</option>
        <option value="MOLETE" {{ old('location') == 'MOLETE' ? 'selected' : '' }}>MOLETE</option>
        <option value="Monatan" {{ old('location') == 'Monatan' ? 'selected' : '' }}>Monatan</option>
        <option value="MOWE IBAFO" {{ old('location') == 'MOWE IBAFO' ? 'selected' : '' }}>MOWE IBAFO</option>
        <option value="OFFA" {{ old('location') == 'OFFA' ? 'selected' : '' }}>OFFA</option>
        <option value="OGBOMOSO" {{ old('location') == 'OGBOMOSO' ? 'selected' : '' }}>OGBOMOSO</option>
        <option value="Ojoo" {{ old('location') == 'Ojoo' ? 'selected' : '' }}>Ojoo</option>
        <option value="OLUMO" {{ old('location') == 'OLUMO' ? 'selected' : '' }}>OLUMO</option>
        <option value="Omuaran" {{ old('location') == 'Omuaran' ? 'selected' : '' }}>Omuaran</option>
        <option value="OSOGBO" {{ old('location') == 'OSOGBO' ? 'selected' : '' }}>OSOGBO</option>
        <option value="Ota" {{ old('location') == 'Ota' ? 'selected' : '' }}>Ota</option>
        <option value="Oyo" {{ old('location') == 'Oyo' ? 'selected' : '' }}>Oyo</option>
        <option value="SAGAMU" {{ old('location') == 'SAGAMU' ? 'selected' : '' }}>SAGAMU</option>
        <option value="SANGO" {{ old('location') == 'SANGO' ? 'selected' : '' }}>SANGO</option>

        {{-- Regional Offices --}}
        <option value="Regional Office, Ibadan" {{ old('location') == 'Regional Office, Ibadan' ? 'selected' : '' }}>Regional Office, Ibadan</option>
        <option value="Regional Office, Ogun" {{ old('location') == 'Regional Office, Ogun' ? 'selected' : '' }}>Regional Office, Ogun</option>
        <option value="Regional Office, Osun" {{ old('location') == 'Regional Office, Osun' ? 'selected' : '' }}>Regional Office, Osun</option>
        <option value="Regional Office, Oyo" {{ old('location') == 'Regional Office, Oyo' ? 'selected' : '' }}>Regional Office, Oyo</option>
        <option value="Regional Office, Kwara" {{ old('location') == 'Regional Office, Kwara' ? 'selected' : '' }}>Regional Office, Kwara</option>
    </select>
</div>

                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>{{ strtoupper($role) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Create Staff</button>
            </form>
        </div>
    </div>

    {{-- Staff List --}}
    <div class="card">
        <div class="card-header">Existing Staff</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Region</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $s)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $s->name }}</td>
                            <td>{{ $s->email }}</td>
                            <td>{{ strtoupper($s->role) }}</td>
                            <td>{{ $s->location }}</td>
                            <td>
                                <a href="{{ route('staff.edit', $s->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                <form action="{{ route('staff.destroy', $s->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No staff found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
