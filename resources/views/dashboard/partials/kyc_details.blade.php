<div class="row">
    <div class="col-md-6">
        <h6>Old Data (From System)</h6>
        <table class="table table-sm">
            <tr><th>Name</th><td>{{ $old->Surname ?? $old->OtherNames ?? '-' }} {{ $old->FirstName ?? $old->Surname ?? '' }}</td></tr>
            <tr><th>Address</th><td>{{ $old->Address ?? $old->Address1 ?? '-' }}</td></tr>
            <tr><th>Phone</th><td>{{ $old->Mobile ?? $old->Phone ?? '-' }}</td></tr>
            <tr><th>Email</th><td>{{ $old->Email ?? '-' }}</td></tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6>New Data (Customer Update)</h6>
        <table class="table table-sm">
            <tr><th>Name</th><td>{{ $form->fullname }}</td></tr>
            <tr><th>Address</th><td>{{ $form->address }}</td></tr>
            <tr><th>Phone</th><td>{{ $form->phone }}</td></tr>
            <tr><th>Email</th><td>{{ $form->email }}</td></tr>
            <tr><th>NIN</th><td>{{ $form->nin }}</td></tr>
        </table>
    </div>
</div>
<hr>
<h6>Documents</h6>
@if($form->document_path)
    @foreach(explode(',', $form->document_path) as $doc)
        <a href="{{ asset('storage/'.trim($doc)) }}" target="_blank" class="btn btn-sm btn-outline-primary me-2">View Document</a>
    @endforeach
@else
    No documents
@endif

<hr>
<h6>Audit Trail</h6>
<ul>
    <li>RKAM: {{ $form->rkam_status }} @if($form->rkam_reviewed_at) on {{ $form->rkam_reviewed_at }} @endif</li>
    <li>BM: {{ $form->bm_status }} @if($form->bm_reviewed_at) on {{ $form->bm_reviewed_at }} @endif</li>
    <li>RICO: {{ $form->rico_status ?? 'Pending' }}</li>
    <li>Billing: {{ $form->billing_status ?? 'Pending' }}</li>
</ul>