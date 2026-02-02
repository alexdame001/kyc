<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;



class CustomerDataController extends Controller
{
    public function fetchCustomerData(Request $request)
{
    $request->validate([
        'account_type' => 'required|in:prepaid,postpaid',
        'account_id' => 'required|string',
    ]);

    $type = strtolower($request->input('account_type'));
    $id = $request->input('account_id');

    if ($type === 'postpaid') {
        // Fetch from EMS database using account number
        $customer = DB::connection('sqlsrv89')
            ->table('CustomerNew')
            ->select('Surname', 'FirstName', DB::raw("ISNULL(Address1, '') + ' ' + ISNULL(Address2, '') AS Address"), 'Email', 'Mobile')
            ->where('AccountNo', $id)
            ->first();

        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Postpaid customer not found for the given account number.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Postpaid customer data retrieved successfully.',
            'data' => $customer,
        ]);
    }

    if ($type === 'prepaid') {
        // Fetch from ECMI database using meter number
        $customer = DB::connection('sqlsrv81')
            ->table('Customers')
            ->select('Surname', 'OtherNames', DB::raw('Address'), 'Email', 'Mobile')
            ->where('MeterNo', $id)
            ->first();

        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Prepaid customer not found for the given meter number.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Prepaid customer data retrieved successfully.',
            'data' => $customer,
        ]);
    }

    return response()->json([
        'status' => false,
        'message' => 'Invalid account type provided.',
    ], 422);
}



public function updateIdentification(Request $request)
{
    Log::info('updateIdentification called');

    $request->validate([
        'account_type' => 'required|in:prepaid,postpaid',
        'account_id' => 'required|string',
        'name' => 'nullable|string',
        'address' => 'nullable|string',
        'email' => 'nullable|email',
        'phonenumber' => 'nullable|string',
        'supporting_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    ]);

    $requiresDocument = $request->filled('name') || $request->filled('address');

    if ($requiresDocument && !$request->hasFile('supporting_document')) {
        Log::warning('Missing document upload when required');
        return response()->json([
            'status' => false,
            'message' => 'Name or address change requires a valid document upload.',
        ], 422);
    }

    try {
        $filePath = null;
        if ($request->hasFile('supporting_document')) {
            $file = $request->file('supporting_document');
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/kyc_docs', $fileName);
            $filePath = 'kyc_docs/' . $fileName;
            Log::info('File stored at: ' . $filePath);
        }

        DB::table('kyc_forms')->insert([
            'account_type' => $request->input('account_type'),
            'account_id' => $request->input('account_id'),
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'email' => $request->input('email'),
            'phonenumber' => $request->input('phonenumber'),
            'document_path' => $filePath,
            'submitted_at' => now(),
            'status' => 'pending',
        ]);

        Log::info('Data inserted into kyc_forms');

        return response()->json([
            'status' => true,
            'message' => 'Identification update request submitted successfully.',
        ]);
    } catch (\Exception $e) {
        Log::error('Exception in updateIdentification: ' . $e->getMessage());

        return response()->json([
            'status' => false,
            'message' => 'Failed to submit identification update: ' . $e->getMessage(),
        ], 500);
    }
}


}


