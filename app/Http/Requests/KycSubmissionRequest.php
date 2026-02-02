<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KycSubmissionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'Surname'          => 'required|string|max:255',
            'FirstName'       => 'required|string|max:255',
            'OtherName'       => 'required|string|max:255',
            'OldName'        =>  'nullable|string|max:255',
            'account_no'   => [
                'required',
                'string',
                // Must match: two digits "/" two digits "/" two digits "/" four digits "-" two digits
                'regex:/^[0-9]{2}\/[0-9]{2}\/[0-9]{2}\/[0-9]{4}-[0-9]{2}$/',
                'unique:kyc_forms,account_no',
            ],
            'national_id_number' => [
                'required',
                'string',
                'regex:/^[0-9]{11}$/',
                'unique:kyc_forms,national_id_number',
            ],
            
            'phone_number'       => 'required|string|min:11|max:15',
            'address'            => 'required|string|max:1000',
            'email'              => 'nullable|email|max:255',
            'occupancy_status'   => 'required|in:Tenant,Landlord,landlord,tenant',
        ];
    }

    public function messages()
    {
        return [
            'account_no.regex' => 'The account number must follow the format "XX/XX/XX/XXXX-XX" (e.g. 11/11/08/0017-01).',
            'account_no.unique' => 'That account number has already been registered.',
            'national_id_number.regex' => 'The NIN must be exactly 11 digits.',
        ];
    }
}
