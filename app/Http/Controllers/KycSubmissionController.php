<?php

namespace App\Http\Controllers;

use App\Http\Requests\KycSubmissionRequest;
use App\Models\KycForm;
use Illuminate\Http\JsonResponse;

class KycSubmissionController extends Controller
{
    /**
     * Handle a new KYC submission.
     */
    public function store(KycSubmissionRequest $request): JsonResponse
    {
        // Validate + get only the permitted data
        $data = $request->validated();

        // Create the record
        $kyc = KycForm::create([
            'Surname'            => $data['Surname'],
            'FirstName'         => $data['FirstName'],
            'OtherName'        => $data['OtherName'],
            'OldName'         => $data['OldName'],
            'account_no'     => $data['account_no'],
            'national_id_number'   => $data['national_id_number'],
            'phone_number'         => $data['phone_number'],
            'address'              => $data['address'],
            'email'                => $data['email'] ?? null,
            'occupancy_status'     => $data['occupancy_status'],
        ]);

        // Return success response
        return response()->json([
            'message' => 'KYC submitted successfully',
            'data'    => $kyc,
        ], 201);
    }
}
