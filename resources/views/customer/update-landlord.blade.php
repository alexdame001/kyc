@extends('layouts.background')

@section('title', 'Landlord KYC Update')

@section('content')

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.login-container{display:flex;justify-content:center;align-items:center;min-height:100vh}
.login-card{background:rgba(255,255,255,.9);padding:2rem;max-width:500px;width:100%;border-radius:.5rem}
.preview-box{
    background:#f8f9fa;
    border:1px dashed #ccc;
    padding:10px;
    margin-top:8px;
    font-weight:bold;
}
</style>
</head>

<body>

<div class="login-container">
<div class="login-card">

<h3 class="text-center mb-3">KYC Update Form (Landlord)</h3>

<form action="{{ route('customer.update.landlord.submit') }}" method="POST" enctype="multipart/form-data">
@csrf

{{-- NIN --}}
<div class="mb-3">
<label>NIN</label>
<input type="text" name="nin" class="form-control" value="{{ old('nin',$customer->NIN ?? '') }}" required>
</div>

<div class="mb-3">
<label>Upload NIN Image</label>
<input type="file" name="nin_image" class="form-control" required>
</div>

{{-- ================= NAME ================= --}}

@if (in_array('name', $updateFields))

<div class="mb-3">
<label>Surname</label>
<input type="text" name="surname" id="surname" class="form-control"
value="{{ old('surname',$customer->Surname ?? '') }}" oninput="updatePreview()" required>
</div>

<div class="mb-3">
<label>First Name</label>
<input type="text" name="firstname" id="firstname" class="form-control"
value="{{ old('firstname',$customer->Firstname ?? '') }}" oninput="updatePreview()" required>
</div>

<div class="mb-3">
<label>Other Name (Middle Name)</label>
<input type="text" name="othername" id="othername" class="form-control"
value="{{ old('othername',$customer->OtherName ?? '') }}" oninput="updatePreview()">
</div>

{{-- FULLNAME PREVIEW --}}

<div class="preview-box">
Final Full Name Preview:
<div id="fullnamePreview">---</div>
</div>

<div class="mb-3 mt-3">
<label>Update Name Type</label>
<select name="name_update_type" class="form-control" onchange="toggleNameFields()" required>
<option value="">-- Select --</option>
<option value="correction">Correction</option>
<option value="change">Change</option>
</select>
</div>

{{-- CORRECTION --}}

<div id="correction-fields" style="display:none">

<div class="mb-3">
<label>Corrected Full Name (Surname Firstname Othername)</label>
<input type="text" name="corrected_fullname" id="corrected_fullname"
class="form-control" oninput="updateCorrectionPreview()"
placeholder="ADEJO JOHN MICHAEL">
</div>

<div class="preview-box">
Corrected Name Preview:
<div id="correctedPreview">---</div>
</div>

<div class="alert alert-info mt-2">
NIN image will verify corrected name.
</div>

</div>

{{-- CHANGE --}}

<div id="change-fields" style="display:none">

<div class="mb-3">
<label>New Full Name (Surname Firstname Othername)</label>
<input type="text" name="new_fullname" id="new_fullname"
class="form-control" oninput="updateChangePreview()"
placeholder="ADEJO JOHN MICHAEL">
</div>

<div class="preview-box">
New Name Preview:
<div id="changePreview">---</div>
</div>

<div class="mb-3 mt-2">
<label>Upload Court Affidavit</label>
<input type="file" name="name_docs[]" class="form-control" multiple>
</div>

</div>

@endif

{{-- ================= PHONE ================= --}}

@if (in_array('phone', $updateFields))
<div class="mb-3">
<label>Phone</label>
<input type="text" name="phone" class="form-control" value="{{ old('phone',$customer->Phone ?? '') }}">
</div>
@endif

{{-- ================= EMAIL ================= --}}

@if (in_array('email', $updateFields))
<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" value="{{ old('email',$customer->Email ?? '') }}">
</div>
@endif

{{-- ================= ADDRESS ================= --}}

@if (in_array('address', $updateFields))

<div class="mb-3">
<label>Address</label>
<input type="text" name="address" class="form-control"
value="{{ old('address',$customer->Address ?? '') }}" required>
</div>

<div class="mb-3">
<label>Upload Address Documents</label>
<input type="file" name="address_docs[]" class="form-control" multiple>
</div>

@endif

{{-- ================= STATE ================= --}}

<div class="mb-3">
<label>State</label>
<select name="state" class="form-control" required>
<option value="">-- Select --</option>
@foreach($states as $state)
<option value="{{ $state }}">{{ $state }}</option>
@endforeach
</select>
</div>

{{-- ================= BUSINESS ================= --}}

<div class="mb-3">
<label>Line of Business</label>
<select name="line_of_business" id="line_of_business" class="form-control" required>
<option value="">-- Select --</option>
@foreach($businessTypes as $line)
<option value="{{ $line }}">{{ $line }}</option>
@endforeach
</select>
</div>

<div class="mb-3" id="other_line_div" style="display:none">
<label>Specify Other</label>
<input type="text" name="other_line_of_business" class="form-control">
</div>

{{-- ================= BUILDING ================= --}}

<div class="mb-3">
<label>Building Type</label>
<select name="building_type" class="form-control" required>
<option value="">-- Select --</option>
@foreach($buildingTypes as $type)
<option value="{{ $type }}">{{ $type }}</option>
@endforeach
</select>
</div>

<button class="btn btn-warning w-100 mt-3">Submit KYC Update</button>

</form>

</div>
</div>

{{-- ================= JAVASCRIPT ================= --}}

<script>

document.addEventListener('DOMContentLoaded', function(){

    function updatePreview(){
        let f = document.getElementById('firstname')?.value || '';
        let o = document.getElementById('othername')?.value || '';
        let s = document.getElementById('surname')?.value || '';

        let preview = (f + " " + o + " " + s).replace(/\s+/g,' ').trim();

        let previewBox = document.getElementById('fullnamePreview');
        if(previewBox){
            previewBox.innerText = preview || '---';
        }
    }

    window.updatePreview = updatePreview;

    window.toggleNameFields = function(){
        let type = document.querySelector('[name="name_update_type"]')?.value;

        let correction = document.getElementById('correction-fields');
        let change = document.getElementById('change-fields');

        if(correction) correction.style.display = type === 'correction' ? 'block' : 'none';
        if(change) change.style.display = type === 'change' ? 'block' : 'none';
    }

    window.updateCorrectionPreview = function(){
        let val = document.getElementById('corrected_fullname')?.value || '';
        let box = document.getElementById('correctedPreview');
        if(box) box.innerText = val || '---';
    }

    window.updateChangePreview = function(){
        let val = document.getElementById('new_fullname')?.value || '';
        let box = document.getElementById('changePreview');
        if(box) box.innerText = val || '---';
    }

    let lineBusiness = document.getElementById('line_of_business');

    if(lineBusiness){
        lineBusiness.addEventListener('change', function(){
            let otherDiv = document.getElementById('other_line_div');
            if(otherDiv){
                otherDiv.style.display = this.value === 'Other' ? 'block' : 'none';
            }
        });
    }

    updatePreview();

});

</script>


@endsection
