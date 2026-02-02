<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CustomerDataControllerPrepaid extends Controller
{
    public function fetchByMeterNo(Request $request)
    {
        $request->validate([
            'meter_number' => 'required|string',
        ]);

        $meterNumber = $request->input('meter_number');

        $customer = DB::connection('sqlsrv81')
            ->table('Customers')
            ->select('Surname', 'OtherNames',  DB::raw('Address'), 'Email', 'Mobile')
            ->where('MeterNo', $meterNumber)
            ->first();

        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer not found for the given meter number.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Customer data retrieved successfully.',
            'data' => $customer,
        ]);
    }
}


