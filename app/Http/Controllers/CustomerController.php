<?php

namespace App\Http\Controllers;

use App\Models\KycForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\support\Facades\Http;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;
use app\http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{


// public function showLandlordForm(Request $request)
// {
//     $customer = session('customer');
//     $accountType = session('account_type');

//     if (!$customer) {
//         return redirect()->route('customer.login')->with('error', 'Session expired, please log in again.');
//     }

//     // Default update fields
//     $updateFields = session('update_fields', ['address', 'phone', 'email']);

//     // Force fields that are N/A
//     if (isset($customer->Email) && $customer->Email === 'N/A') {
//         $updateFields[] = 'email';
//     }
//     if ((isset($customer->Phone) && $customer->Phone === 'N/A') || (isset($customer->Mobile) && $customer->Mobile === 'N/A')) {
//         $updateFields[] = 'phone';
//     }
//     if ((isset($customer->Address) && $customer->Address === 'N/A') || (isset($customer->Address1) && $customer->Address1 === 'N/A')) {
//         $updateFields[] = 'address';
//     }
//     if ((isset($customer->Firstname) && $customer->Firstname === 'N/A') || (isset($customer->Surname) && $customer->Surname === 'N/A')) {
//         $updateFields[] = 'name';
//     }

//     // Ensure line_of_business and building_type are always available for the form
//     $updateFields[] = 'line_of_business';
//     $updateFields[] = 'building_type';

//     // Remove duplicates
//     $updateFields = array_unique($updateFields);
//     session(['update_fields' => $updateFields]);

//     // IBEDC states alphabetically
//     $states = ['Ekiti', 'Kwara', 'Niger', 'Ogun', 'Oyo', 'Osun'];

//     // Line of Business options
//     $businessTypes = [
//         'Artisan',
//         'Bakery',
//         'Buying & Selling of goods',
//         'Farming',
//         'Hotel & Restaurant',
//         'Ice block production',
//         'IT related business',
//         'MDAs - Federal Government',
//         'MDAs - State',
//         'Petrol & gas filling station',
//         'Supermarket',
//         'Telecommunications',
//         'Welding & Fabrication',
//         'Banking, finance & insurance',
//         'Solar power',
//         'Other'
//     ];

//     // Building types
//     $buildingTypes = [
//         'Commercial',
//         'Industrial',
//         'Mixed-use',
//         'Residential',
//         'Other'
//     ];

//     return view('customer.update-landlord', compact(
//         'customer', 
//         'accountType', 
//         'updateFields', 
//         'states', 
//         'businessTypes', 
//         'buildingTypes'
//     ));
// }


// public function submitLandlordForm(Request $request)
// {
//     if (!session()->has('customer')) {
//         return redirect()->route('customer.login.form')->with('error', 'Please log in first.');
//     }

//     $customer = session('customer'); // make sure session has the right keys
//     $type = session('account_type');
//     $fields = session('update_fields', []); // fields user wants to update

//     // Map line_of_business to business_type
//     if ($request->line_of_business === 'Other') {
//         $request->merge(['business_type' => $request->other_line_of_business]);
//     } else {
//         $request->merge(['business_type' => $request->line_of_business]);
//     }

//     // Base validation
//     $rules = [
//         'nin' => 'required|string|size:11',
//         'state' => 'required|string|max:100',
//         'building_type' => 'required|string',
//         'business_type' => 'required|string',
//         'supporting_doc.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
//     ];

//     // Conditional fields
//     if (in_array('name', $fields)) {
//         $rules['firstname'] = 'required|string|max:100';
//         $rules['surname'] = 'required|string|max:100';
//         $rules['name_update_type'] = 'required|string|in:correction,change';

//         if ($request->name_update_type === 'correction') {
//             $rules['corrected_name'] = 'required|string|max:150';
//             $rules['nin_docs.*'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
//         } elseif ($request->name_update_type === 'change') {
//             $rules['new_name'] = 'required|string|max:150';
//             $rules['name_docs.*'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
//         }
//     }

//     if (in_array('address', $fields)) {
//         $rules['address'] = 'required|string|max:255';
//         $rules['address_docs.*'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
//     }

//     if (in_array('phone', $fields)) {
//         $rules['phone'] = 'nullable|string|max:20';
//         $rules['phone_docs.*'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
//     }

//     if (in_array('email', $fields)) {
//         $rules['email'] = 'nullable|email|max:255';
//         $rules['email_docs.*'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
//     }

//     $request->validate($rules);

//     // Account ID
//     $accountId = $type === 'postpaid' ? $customer->AccountNo ?? $customer->account_id : $customer->MeterNo ?? $customer->account_id;

//     // Fullname fix
//     $firstname = $request->firstname ?? ($customer->firstname ?? '');
//     $surname = $request->surname ?? ($customer->surname ?? '');
//     $fullname = trim($firstname . ' ' . $surname);

//     // Address
//     $address = $request->address ?? ($customer->address ?? '');

//     // Prevent duplicate pending KYC
//     $exists = DB::table('kyc_forms')
//         ->where('account_id', $accountId)
//         ->where('rico_status', 'pending')
//         ->exists();

//     if ($exists) {
//         return redirect()->route('customer.dashboard')->with('error', 'You already have a pending KYC update.');
//     }

//     // Handle supporting documents
//     $documentPaths = [];

//     $docFields = [
//         'supporting_doc' => '',
//         'nin_docs' => 'name_update_type' === 'correction',
//         'name_docs' => 'name_update_type' === 'change',
//         'address_docs' => 'address',
//         'phone_docs' => 'phone',
//         'email_docs' => 'email',
//     ];

//     foreach ($docFields as $input => $condition) {
//         if (($condition === true || in_array($condition, $fields)) && $request->hasFile($input)) {
//             foreach ($request->file($input) as $doc) {
//                 $filename = $fullname . '_' . $input . '_' . time() . '.' . $doc->getClientOriginalExtension();
//                 $documentPaths[] = $doc->storeAs('kyc_documents', $filename, 'public');
//             }
//         }
//     }

//     // Insert into DB
//     $data = [
//         'account_type' => $type,
//         'account_id' => $accountId,
//         'fullname' => in_array('name', $fields) ? $fullname : null,
//         'old_fullname' => $customer->fullname ?? null,
//         'address' => in_array('address', $fields) ? $address : null,
//         'old_address' => $customer->address ?? null,
//         'phone' => in_array('phone', $fields) ? $request->phone : null,
//         'old_phone' => $customer->phone ?? null,
//         'email' => in_array('email', $fields) ? $request->email : null,
//         'old_email' => $customer->email ?? null,
//         'state' => $request->state,
//         'nin' => $request->nin,
//         'building_type' => $request->building_type,
//         'business_type' => $request->business_type,
//         'document_path' => implode(',', $documentPaths),
//         'occupancy_status' => 'landlord',
//         'submitted_at' => now(),
//         'submitted_by' => $accountId,
//         'rkam_status' => 'pending',
//         'bm_status' => 'pending',
//         'rico_status' => 'pending',
//         'billing_status' => 'pending',
//         'audit_status' => 'pending',
//         'admin_status' => 'pending',
//     ];

//     DB::table('kyc_forms')->insert($data);

//     return redirect()->route('customer.dashboard')->with('success', 'Your KYC update as landlord has been submitted.');
// }


// public function submitLandlordForm(Request $request)
// {
//     if (!session()->has('customer')) {
//         return redirect()->route('customer.login.form')->with('error', 'Please log in first.');
//     }

//     $customer = session('customer');
//     $type = session('account_type');
//     $fields = session('update_fields', []);

//     // Map line_of_business to business_type
//     if ($request->line_of_business === 'Other') {
//         $request->merge(['business_type' => $request->other_line_of_business]);
//     } else {
//         $request->merge(['business_type' => $request->line_of_business]);
//     }

//     // Base validation (unchanged)
//     $rules = [
//         'nin' => 'required|string|size:11',
//         'state' => 'required|string|max:100',
//         'building_type' => 'required|string',
//         'business_type' => 'required|string',
//         'supporting_doc.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
//     ];

//     // Conditional fields (unchanged)
//     if (in_array('name', $fields)) {
//         $rules['firstname'] = 'required|string|max:100';
//         $rules['surname'] = 'required|string|max:100';
//         $rules['name_update_type'] = 'required|string|in:correction,change';

//         if ($request->name_update_type === 'correction') {
//             $rules['corrected_name'] = 'required|string|max:150';
//             $rules['nin_docs.*'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
//         } elseif ($request->name_update_type === 'change') {
//             $rules['new_name'] = 'required|string|max:150';
//             $rules['name_docs.*'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
//         }
//     }

//     if (in_array('address', $fields)) {
//         $rules['address'] = 'required|string|max:255';
//         $rules['address_docs.*'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
//     }

//     if (in_array('phone', $fields)) {
//         $rules['phone'] = 'nullable|string|max:20';
//         $rules['phone_docs.*'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
//     }

//     if (in_array('email', $fields)) {
//         $rules['email'] = 'nullable|email|max:255';
//         $rules['email_docs.*'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
//     }

//     $request->validate($rules);

//     // Account ID (unchanged)
//     $accountId = $type === 'postpaid' ? $customer->AccountNo ?? $customer->account_id : $customer->MeterNo ?? $customer->account_id;

//     // Construct old/new values consistently (handle varying customer keys)
//     $oldFirstname = $type === 'postpaid' ? ($customer->FirstName ?? '') : ($customer->OtherNames ?? '');
//     $oldSurname = $customer->Surname ?? '';
//     $oldFullname = trim($oldFirstname . ' ' . $oldSurname);
//     $oldAddress = $type === 'postpaid' ? trim(($customer->Address1 ?? '') . ' ' . ($customer->Address2 ?? '')) : ($customer->Address ?? '');
//     $oldPhone = $customer->Mobile ?? $customer->Phone ?? '';
//     $oldEmail = $customer->Email ?? '';

//     $newFirstname = $request->firstname ?? $oldFirstname;
//     $newSurname = $request->surname ?? $oldSurname;
//     $newFullname = trim($newFirstname . ' ' . $newSurname);
//     $newAddress = $request->address ?? $oldAddress;
//     $newPhone = $request->phone ?? $oldPhone;
//     $newEmail = $request->email ?? $oldEmail;

//     // Duplicate check (unchanged)
//     $exists = DB::table('kyc_forms')
//         ->where('account_id', $accountId)
//         ->where('rico_status', 'pending')
//         ->exists();

//     if ($exists) {
//         return redirect()->route('customer.dashboard')->with('error', 'You already have a pending KYC update.');
//     }

//     // Handle documents dynamically
//     $documentPaths = [];
//     $fullnameForFilename = preg_replace('/[^\w\-]/', '_', $newFullname); // Clean for FS

//     // Always-required docs
//     if ($request->hasFile('supporting_doc')) {
//         foreach ($request->file('supporting_doc') as $doc) {
//             $ext = $doc->getClientOriginalExtension();
//             $filename = "{$fullnameForFilename}_supporting_" . time() . rand(1000, 9999) . ".{$ext}"; // Add rand to avoid collisions
//             $path = $doc->storeAs('kyc_documents', $filename, 'public');
//             $documentPaths[] = $path;
//         }
//     }

//     // Conditional docs
//     if (in_array('name', $fields)) {
//         $nameDocInput = $request->name_update_type === 'correction' ? 'nin_docs' : 'name_docs';
//         if ($request->hasFile($nameDocInput)) {
//             foreach ($request->file($nameDocInput) as $doc) {
//                 $ext = $doc->getClientOriginalExtension();
//                 $filename = "{$fullnameForFilename}_name_{$request->name_update_type}_" . time() . rand(1000, 9999) . ".{$ext}";
//                 $path = $doc->storeAs('kyc_documents', $filename, 'public');
//                 $documentPaths[] = $path;
//             }
//         }
//     }

//     if (in_array('address', $fields) && $request->hasFile('address_docs')) {
//         foreach ($request->file('address_docs') as $doc) {
//             $ext = $doc->getClientOriginalExtension();
//             $filename = "{$fullnameForFilename}_address_" . time() . rand(1000, 9999) . ".{$ext}";
//             $path = $doc->storeAs('kyc_documents', $filename, 'public');
//             $documentPaths[] = $path;
//         }
//     }

//     if (in_array('phone', $fields) && $request->hasFile('phone_docs')) {
//         foreach ($request->file('phone_docs') as $doc) {
//             $ext = $doc->getClientOriginalExtension();
//             $filename = "{$fullnameForFilename}_phone_" . time() . rand(1000, 9999) . ".{$ext}";
//             $path = $doc->storeAs('kyc_documents', $filename, 'public');
//             $documentPaths[] = $path;
//         }
//     }

//     if (in_array('email', $fields) && $request->hasFile('email_docs')) {
//         foreach ($request->file('email_docs') as $doc) {
//             $ext = $doc->getClientOriginalExtension();
//             $filename = "{$fullnameForFilename}_email_" . time() . rand(1000, 9999) . ".{$ext}";
//             $path = $doc->storeAs('kyc_documents', $filename, 'public');
//             $documentPaths[] = $path;
//         }
//     }

//     // Prep old/new for audit (optional: pass to log if you add it)
//     $oldValues = [
//         'fullname' => $oldFullname,
//         'address' => $oldAddress,
//         'phone' => $oldPhone,
//         'email' => $oldEmail,
//     ];
//     $newValues = [
//         'fullname' => $newFullname,
//         'address' => $newAddress,
//         'phone' => $newPhone,
//         'email' => $newEmail,
//     ];

//     // Insert into DB (with try-catch for resilience)
//     $data = [
//         'account_type' => $type,
//         'account_id' => $accountId,
//         'fullname' => in_array('name', $fields) ? $newFullname : $oldFullname, // Always set current if not updating
//         'old_fullname' => $oldFullname,
//         'address' => in_array('address', $fields) ? $newAddress : $oldAddress,
//         'old_address' => $oldAddress,
//         'phone' => in_array('phone', $fields) ? $newPhone : $oldPhone,
//         'old_phone' => $oldPhone,
//         'email' => in_array('email', $fields) ? $newEmail : $oldEmail,
//         'old_email' => $oldEmail,
//         'state' => $request->state,
//         'nin' => $request->nin,
//         'building_type' => $request->building_type,
//         'business_type' => $request->business_type,
//         'document_path' => !empty($documentPaths) ? implode(',', $documentPaths) : null,
//         'occupancy_status' => 'landlord',
//         'submitted_at' => now(),
//         'submitted_by' => $accountId,
//         'rkam_status' => 'pending',
//         'bm_status' => 'pending',
//         'rico_status' => 'pending',
//         'billing_status' => 'pending',
//         'audit_status' => 'pending',
//         'admin_status' => 'pending',
//     ];

//     try {
//         DB::table('kyc_forms')->insert($data);

//         // Log the submit (adapt if you have a specific KYC audit method like in tenant)
//         $this->logAudit(
//             $accountId,
//             $request->ip(),
//             'kyc_submit_landlord',
//             "Landlord KYC submitted for account {$accountId}. Fields: " . implode(', ', $fields),
//             $oldValues,
//             $newValues
//         );

//         // Optional: Clear session fields to reset for next time
//         session()->forget('update_fields');

//         return redirect()->route('customer.dashboard')->with('success', 'Your KYC update as landlord has been submitted.');
//     } catch (\Exception $e) {
//         Log::error("KYC insert failed for {$accountId}: " . $e->getMessage());
//         return redirect()->route('customer.dashboard')->with('error', 'Submission failed—please try again.');
//     }
// }



public function showLandlordForm(Request $request)
{
    $customer = session('customer');
    $accountType = session('account_type');

    if (!$customer) {
        return redirect()->route('customer.login')->with('error', 'Session expired, please log in again.');
    }

    // Default update fields
    $updateFields = session('update_fields', ['address', 'phone', 'email']);

    // Force fields that are N/A
    if (isset($customer->Email) && $customer->Email === 'N/A') {
        $updateFields[] = 'email';
    }
    if ((isset($customer->Phone) && $customer->Phone === 'N/A') || (isset($customer->Mobile) && $customer->Mobile === 'N/A')) {
        $updateFields[] = 'phone';
    }
    if ((isset($customer->Address) && $customer->Address === 'N/A') || (isset($customer->Address1) && $customer->Address1 === 'N/A')) {
        $updateFields[] = 'address';
    }
   if (
    empty($customer->Firstname) || $customer->Firstname === 'N/A' ||
    empty($customer->Surname) || $customer->Surname === 'N/A' ||
    empty($customer->OtherName) || $customer->OtherName === 'N/A'
) {
    $updateFields[] = 'name';
}

    // Ensure line_of_business and building_type are always available for the form
    $updateFields[] = 'line_of_business';
    $updateFields[] = 'building_type';

    // Remove duplicates
    $updateFields = array_unique($updateFields);
    session(['update_fields' => $updateFields]);

    // IBEDC states alphabetically
    $states = ['Ekiti', 'Kwara', 'Niger', 'Ogun', 'Oyo', 'Osun'];

    // Line of Business options
    $businessTypes = [
        'Artisan',
        'Bakery',
        'Buying & Selling of goods',
        'Farming',
        'Hotel & Restaurant',
        'Ice block production',
        'IT related business',
        'MDAs - Federal Government',
        'MDAs - State',
        'Petrol & gas filling station',
        'Supermarket',
        'Telecommunications',
        'Welding & Fabrication',
        'Banking, finance & insurance',
        'Solar power',
        'Other'
    ];

    // Building types
    $buildingTypes = [
        'Commercial',
        'Industrial',
        'Mixed-use',
        'Residential',
        'Other'
    ];

    return view('customer.update-landlord', compact(
        'customer', 
        'accountType', 
        'updateFields', 
        'states', 
        'businessTypes', 
        'buildingTypes'
    ));
}

public function submitLandlordForm(Request $request)
{
    if (!session()->has('customer')) {
        return redirect()->route('customer.login.form')->with('error', 'Please log in first.');
    }

    $customer = session('customer');
    $type = session('account_type');
    $fields = session('update_fields', []);

    // Map line_of_business
    if ($request->line_of_business === 'Other') {
        $request->merge(['business_type' => $request->other_line_of_business]);
    } else {
        $request->merge(['business_type' => $request->line_of_business]);
    }

    // ================= BASE VALIDATION =================

    $rules = [
        'nin' => 'required|string|size:11',
        'nin_image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'state' => 'required|string|max:100',
        'building_type' => 'required|string',
        'business_type' => 'required|string',
    ];

    // ================= NAME VALIDATION =================

    if (in_array('name', $fields)) {

        $rules['firstname'] = 'required|string|max:100';
        $rules['surname'] = 'required|string|max:100';
        $rules['othername'] = 'nullable|string|max:100';
        $rules['name_update_type'] = 'required|in:correction,change';

        if ($request->name_update_type === 'correction') {
            $rules['corrected_fullname'] = 'required|string|max:200';
        }

        if ($request->name_update_type === 'change') {
            $rules['new_fullname'] = 'required|string|max:200';
            $rules['name_docs.*'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
        }
    }

    // ================= ADDRESS =================

    if (in_array('address', $fields)) {
        $rules['address'] = 'required|string|max:255';
        $rules['address_docs.*'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
    }

    // ================= PHONE =================

    if (in_array('phone', $fields)) {
        $rules['phone'] = 'nullable|string|max:20';
    }

    // ================= EMAIL =================

    if (in_array('email', $fields)) {
        $rules['email'] = 'nullable|email|max:255';
    }

    $request->validate($rules);

    // ================= ACCOUNT =================

    $accountId = $type === 'postpaid'
        ? $customer->AccountNo ?? $customer->account_id
        : $customer->MeterNo ?? $customer->account_id;

    // ================= OLD VALUES =================

    $oldFirstname = $customer->Firstname ?? '';
    $oldOthername = $customer->OtherName ?? '';
    $oldSurname  = $customer->Surname ?? '';

    $oldFullname = trim($oldFirstname . ' ' . $oldOthername . ' ' . $oldSurname);

    $oldAddress = $type === 'postpaid'
        ? trim(($customer->Address1 ?? '') . ' ' . ($customer->Address2 ?? ''))
        : ($customer->Address ?? '');

    $oldPhone = $customer->Mobile ?? $customer->Phone ?? '';
    $oldEmail = $customer->Email ?? '';

    // ================= NEW VALUES (DEFAULT) =================

    $newFirstname = $request->firstname ?? $oldFirstname;
    $newOthername = $request->othername ?? $oldOthername;
    $newSurname  = $request->surname ?? $oldSurname;

    // ================= CORRECTION / CHANGE LOGIC =================

    if (in_array('name', $fields)) {

        if ($request->name_update_type === 'correction') {

            $parts = preg_split('/\s+/', trim($request->corrected_fullname));

            $newSurname  = $parts[0] ?? '';
            $newFirstname = $parts[1] ?? '';
            $newOthername = $parts[2] ?? '';

        }

        if ($request->name_update_type === 'change') {

            $parts = preg_split('/\s+/', trim($request->new_fullname));

            $newSurname  = $parts[0] ?? '';
            $newFirstname = $parts[1] ?? '';
            $newOthername = $parts[2] ?? '';
        }
    }

    $newFullname = trim($newFirstname . ' ' . $newOthername . ' ' . $newSurname);

    $newAddress = $request->address ?? $oldAddress;
    $newPhone = $request->phone ?? $oldPhone;
    $newEmail = $request->email ?? $oldEmail;

    // ================= DUPLICATE CHECK =================

    $exists = DB::table('kyc_forms')
        ->where('account_id', $accountId)
        ->where('rico_status', 'pending')
        ->exists();

    if ($exists) {
        return redirect()->route('customer.dashboard')
            ->with('error', 'You already have a pending KYC update.');
    }

    // ================= DOCUMENT UPLOAD =================

    $documentPaths = [];
    $ninImagePath = null;

    $fullnameForFilename = preg_replace('/[^\w\-]/', '_', $newFullname);

    // NIN image
    if ($request->hasFile('nin_image')) {

        $doc = $request->file('nin_image');
        $ext = $doc->getClientOriginalExtension();

        $filename = "{$fullnameForFilename}_nin_" . time() . rand(1000,9999) . ".{$ext}";

        $ninImagePath = $doc->storeAs('kyc_documents', $filename, 'public');

        $documentPaths[] = $ninImagePath;
    }

    // Name change docs
    if (
        in_array('name', $fields) &&
        $request->name_update_type === 'change' &&
        $request->hasFile('name_docs')
    ) {

        foreach ($request->file('name_docs') as $doc) {

            $ext = $doc->getClientOriginalExtension();
            $filename = "{$fullnameForFilename}_affidavit_" . time() . rand(1000,9999) . ".{$ext}";
            $path = $doc->storeAs('kyc_documents', $filename, 'public');

            $documentPaths[] = $path;
        }
    }

    // Address docs
    if (in_array('address', $fields) && $request->hasFile('address_docs')) {

        foreach ($request->file('address_docs') as $doc) {

            $ext = $doc->getClientOriginalExtension();
            $filename = "{$fullnameForFilename}_address_" . time() . rand(1000,9999) . ".{$ext}";
            $path = $doc->storeAs('kyc_documents', $filename, 'public');

            $documentPaths[] = $path;
        }
    }

    // ================= AUDIT VALUES =================

    $oldValues = [
        'fullname' => $oldFullname,
        'address' => $oldAddress,
        'phone' => $oldPhone,
        'email' => $oldEmail,
    ];

    $newValues = [
        'fullname' => $newFullname,
        'address' => $newAddress,
        'phone' => $newPhone,
        'email' => $newEmail,
    ];

    // ================= INSERT =================

    $data = [

        'firstname' => $newFirstname,
        'othername' => $newOthername,
        'surname' => $newSurname,

        'fullname' => in_array('name', $fields) ? $newFullname : $oldFullname,
        'old_fullname' => $oldFullname,

        'address' => in_array('address', $fields) ? $newAddress : $oldAddress,
        'old_address' => $oldAddress,

        'phone' => in_array('phone', $fields) ? $newPhone : $oldPhone,
        'old_phone' => $oldPhone,

        'email' => in_array('email', $fields) ? $newEmail : $oldEmail,
        'old_email' => $oldEmail,

        'account_type' => $type,
        'account_id' => $accountId,

        'state' => $request->state,
        'nin' => $request->nin,

        'nin_doc_path' => $ninImagePath,

        'building_type' => $request->building_type,
        'business_type' => $request->business_type,
        'line_of_business' => $request->business_type,

        'document_path' => !empty($documentPaths) ? implode(',', $documentPaths) : null,

        'occupancy_status' => 'landlord',

        'submitted_at' => now(),
        'updated_at' => now(),

        'submitted_by' => $accountId,

        'status' => 'pending',
        'rkam_status' => 'pending',
        'bm_status' => 'pending',
        'rico_status' => 'pending',
        'billing_status' => 'pending',
        'audit_status' => 'pending',
        'admin_status' => 'pending',
    ];

    try {

        DB::table('kyc_forms')->insert($data);

        $this->logAudit(
            $accountId,
            $request->ip(),
            'kyc_submit_landlord',
            "Landlord KYC submitted",
            $oldValues,
            $newValues
        );

        session()->forget('update_fields');

        return redirect()->route('customer.dashboard')
            ->with('success', 'Your KYC update has been submitted successfully.');

    } catch (\Exception $e) {

        Log::error("KYC submission failed for {$accountId}: " . $e->getMessage());

        return redirect()->route('customer.dashboard')
            ->with('error', 'Submission failed — please try again.');
    }
}





private function formatToMSISDN($phone)
{
    $phone = preg_replace('/\D/', '', $phone); // remove non-digits

    if (substr($phone, 0, 1) === '0') {
        $phone = '234' . substr($phone, 1);
    } elseif (substr($phone, 0, 3) !== '234') {
        $phone = '234' . $phone; 
    }

    return $phone;
}


    public function validateNin(Request $request)
    {
        $request->validate([
            'nin' => 'required|string|size:11',
        ]);

        try {
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])
                ->post('https://ipay.ibedc.com:7642/api/V4IBEDC_new_account_setup_sync/process/nin-validation', [
                    'nin' => $request->nin,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (!isset($data['success']) || $data['success'] !== true) {
                    return response()->json([
                        'success' => false,
                        'message' => $data['message'] ?? 'NIN verification failed',
                    ], 422);
                }

                return response()->json([
                    'success' => true,
                    'firstname' => $data['payload']['customer']['payload']['data']['firstname'] ?? null,
                    'surname' => $data['payload']['customer']['payload']['data']['surname'] ?? null,
                    'birthdate' => $data['payload']['customer']['payload']['data']['birthdate'] ?? null,

                    'dob' => $data['data']['dob'] ?? '',

                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'NIN API error',
            ], 500);

        } catch (\Exception $e) {
            Log::error("NIN validation failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error connecting to NIN service',
            ], 500);
        }
    }


    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'bm_status' => 'required|in:approved,rejected'
        ]);

        DB::table('kyc_forms')
            ->whereIn('id', $request->ids)
            ->update([
                'bm_status' => $request->bm_status,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true, 'message' => 'Selected forms updated successfully.']);
    }

    public function showLoginForm()
    {
        return view('auth.customer-login');
    }

    // public function dashboard()
    // {
    //     if (!session()->has('customer')) {
    //         return redirect()->route('customer.login.form')->with('error', 'Please log in first.');
    //     }

    //     $customer = session('customer');

    //     return view('dashboard.customer', compact('customer'));
    // }

      public function dashboard()
    {
        if (!session()->has('customer')) {
            return redirect()->route('customer.login.form')->with('error', 'Please log in first.');
        }

        $customer = session('customer');
    $accountType = session('account_type');

    return view('dashboard.customer', compact('customer', 'accountType'));
    }

    public function showSelectFieldsForm()
{
    if (!session()->has('customer')) {
        return redirect()->route('customer.login.form')->with('error', 'Please log in first.');
    }

    $customer = session('customer');
    $accountType = session('account_type');

    $missingFields = collect();

    if ($accountType === 'postpaid') {
        if (empty($customer->Mobile)) $missingFields->push('Phone');
        if (empty($customer->Email)) $missingFields->push('Email');
        if (empty($customer->Address1) && empty($customer->Address2)) $missingFields->push('Address');
        if (empty($customer->FirstName) || empty($customer->Surname)) $missingFields->push('Name');
    } elseif ($accountType === 'prepaid') {
        if (empty($customer->Phone)) $missingFields->push('Phone');
        if (empty($customer->Email)) $missingFields->push('Email');
        if (empty($customer->Address)) $missingFields->push('Address');
        if (empty($customer->Surname) || empty($customer->OtherNames)) $missingFields->push('Name');
    }

    return view('customer.choose-fields', compact('missingFields'));
}


    public function handleSelectFields(Request $request)
    {
        if (!session()->has('customer')) {
            return redirect()->route('customer.login.form')->with('error', 'Please log in first.');
        }

        $validated = $request->validate([
            'fields' => 'required|array|min:1',
            'fields.*' => 'in:name,address,email,phone',
        ]);

        session(['update_fields' => $validated['fields']]);

        $occupancy = $request->input('occupancy_status');

        if ($occupancy === 'landlord') {
            return redirect()->route('customer.update.landlord.form');
        } elseif ($occupancy === 'tenant') {
            return redirect()->route('customer.update.tenant.form');
        }

        return back()->with('error', 'Invalid occupancy selection.');
    }

    public function chooseOccupancy(Request $request)
    {
        if (!session()->has('customer')) {
            return redirect()->route('customer.login.form')->with('error', 'Please log in first.');
        }

        $selectedFields = $request->fields ?? [];
        session(['selected_update_fields' => $selectedFields]);

        return view('customer.choose');
    }

    public function chooseFieldsToUpdate()
    {
        if (!session()->has('customer')) {
            return redirect()->route('customer.login.form')->with('error', 'Please log in first.');
        }

        return view('customer.choose-fields');
    }

    public function processFieldSelection(Request $request)
    {
        $validated = $request->validate([
            'fields' => 'required|array|min:1',
            'fields.*' => 'in:name,address,email,phone',
        ]);

        session(['update_fields' => $validated['fields']]);

        return redirect()->route('customer.choose.occupancy');
    }





    public function showTenantForm()
    {
        if (!session()->has('customer')) {
            return redirect()->route('customer.login.form')->with('error', 'Please log in first.');
        }

        $customer = session('customer');
        $type = session('account_type');

        return view('customer.update-tenant', compact('customer', 'type'));
    }




    public function chooseUpdateFields()
    {
        if (!session()->has('customer')) {
            return redirect()->route('customer.login.form')->with('error', 'Please log in first.');
        }

        $type = session('account_type');
        $customer = session('customer');

        return view('customer.choose-fields', compact('customer', 'type'));
    }







    public function handleFieldSelection(Request $request)
    {
        $request->validate([
            'fields_to_update' => 'required|array|min:1',
            'occupancy_status' => 'required|in:landlord,tenant',
        ]);

        session([
            'fields_to_update' => $request->fields_to_update,
            'selected_occupancy' => $request->occupancy_status,
        ]);

        return $request->occupancy_status === 'tenant'
            ? redirect()->route('customer.update.tenant.form')
            : redirect()->route('customer.update.landlord.form');
    }



    public function login(Request $request)
    {
        $request->validate([
            'account_type' => 'required|in:prepaid,postpaid',
            'account_id' => 'required|string',
        ]);

        $type = strtolower($request->input('account_type'));
        $id = $request->input('account_id');

        $customer = null;

        if ($type === 'postpaid') {
            $customer = DB::connection('sqlsrv89')
                ->table('customerNew')
                ->where('AccountNo', $id)
                ->first();
        } else { // prepaid
            $customer = DB::connection('sqlsrv81')
                ->table('Customers')
                ->where('MeterNo', $id)
                ->first();
        }

        // Log failed login attempt with detailed description
        if (!$customer) {
            $this->logAudit(
                $id,
                $request->ip(),
                'login_failure',
                "Failed login attempt for account/meter ID: {$id}. Customer not found."
            );
            return back()->with('error', 'Customer not found. Please check your Account Number or Meter Number.');
        }

        // Log successful login attempt with detailed description
        $this->logAudit(
            $id,
            $request->ip(),
            'login_success',
            "Customer with account ID {$id} logged in successfully."
        );

        session([
            'customer' => $customer,
            'account_type' => $type,
        ]);

        return redirect()->route('customer.dashboard')->with('success', 'Login successful! Welcome to your dashboard.');
    }
    /**
     * Helper method to log audit events.
     * This uses the fields available in your provided AuditLog model.
     *
     * @param string $accountId
     * @param string $ipAddress
     * @param string $action
     * @param string $description
     */

    public function submitTenantForm(Request $request)
    {
        if (!session()->has('customer')) {
            return redirect()->route('customer.login.form')->with('error', 'Please log in first.');
        }

        $customer = session('customer');
        $type = session('account_type');
        $fields = session('update_fields', []);

        // Step 1: Define validation rules
        $rules = [
            'tenant_nin' => 'required|string|size:11',
            'knows_landlord' => 'required|in:yes,no',
            'nin' => 'required|string|size:11',
            'state' => 'required|string|max:100',
            'type_of_building' => 'required|string|max:100',
            'business_type' => 'required|string|max:255',
            'supporting_doc' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];

        if (in_array('name', $fields)) {
            $rules['name'] = 'required|string|max:100';
            if ($type === 'postpaid') {
                $rules['Surname'] = 'required|string|max:100';
            }
        }

        if (in_array('address', $fields)) {
            if ($type === 'postpaid') {
                $rules['address1'] = 'required|string|max:150';
                $rules['address2'] = 'nullable|string|max:150';
            } else {
                $rules['address'] = 'required|string|max:255';
            }
        }

        if (in_array('phone', $fields)) {
            $rules['phone'] = 'nullable|string|max:20';
        }

        if (in_array('email', $fields)) {
            $rules['email'] = 'nullable|email|max:255';
        }

        if ($request->knows_landlord === 'yes') {
            $rules['landlord_name'] = 'required|string|max:255';
            $rules['landlord_phone'] = 'required|string|max:20';
            $rules['landlord_address'] = 'required|string|max:255';
        }

        $request->validate($rules);

        // Step 2: Prepare data
        $accountId = $type === 'postpaid' ? $customer->AccountNo : $customer->MeterNo;
        $fullname = $type === 'postpaid' && in_array('name', $fields)
            ? trim($request->name . ' ' . $request->Surname)
            : ($request->name ?? ($customer->OtherNames ?? ''));

        $address = $type === 'postpaid' && in_array('address', $fields)
            ? trim($request->address1 . ' ' . $request->address2)
            : ($request->address ?? $customer->Address ?? '');

        $exists = DB::table('kyc_forms')
            ->where('account_id', $accountId)
            ->where('rico_status', 'pending')
            ->exists();

        if ($exists) {
            return redirect()->route('customer.dashboard')->with('error', 'You already have a pending KYC update.');
        }

        // Step 3: Store supporting doc
        $cleanName = preg_replace('/[^\w\-]/', '_', $fullname);
        $cleanName = str_replace(['/', '\\'], '_', $cleanName);
        $cleanName = basename($cleanName);
        $extension = $request->file('supporting_doc')->getClientOriginalExtension();
        $filename = "{$cleanName}_{$accountId}_" . time() . ".{$extension}";
        $request->file('supporting_doc')->storeAs('kyc_documents', $filename, 'public');

        // Step 4: Prepare insert array
        $data = [
            'account_type' => $type,
            'account_id' => $accountId,
            'fullname' => $fullname,
            'tenant_nin' => $request->tenant_nin,
            'nin' => $request->nin,
            'knows_landlord' => $request->knows_landlord,
            'address' => $address,
            'state' => $request->state,
            'type_of_building' => $request->type_of_building,
            'business_type' => $request->business_type,
            'occupancy_status' => 'tenant',
            'document_path' => "kyc_documents/$filename",
            'submitted_at' => now(),
            'submitted_by' => $accountId,
            'rico_status' => 'pending',
            'billing_status' => 'pending',
            'audit_status' => 'pending',
            'admin_status' => 'pending',
            'nin_validation_status' => 'validated',
        ];

        if (in_array('phone', $fields)) {
            $data['phone'] = $request->phone;
        }

        if (in_array('email', $fields)) {
            $data['email'] = $request->email;
        }

        if ($request->knows_landlord === 'yes') {
            $data['landlord_name'] = $request->landlord_name;
            $data['landlord_phone'] = $request->landlord_phone;
            $data['landlord_address'] = $request->landlord_address;
        }

        // Log the KYC update action with old and new values
        $this->logKycUpdateAudit($customer, $request, $type);

        DB::table('kyc_forms')->insert($data);

        return redirect()->route('customer.dashboard')->with('success', 'Your KYC update as tenant has been submitted.');
    }






    protected function logAudit($accountId, $ipAddress, $action, $description, $oldValues = null, $newValues = null)
    {
        try {
            AuditLog::create([
                'user_id' => $accountId,
                'action' => $action,
                'description' => $description,
                'ip_address' => $ipAddress,
                'user_agent' => request()->header('User-Agent'),
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create audit log entry: ' . $e->getMessage());
            throw $e; // <-- Add this line
        }
    }

}






