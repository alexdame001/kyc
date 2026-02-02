<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycForm extends Model
{
    protected $table = 'kyc_forms';

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    protected $fillable = [
        'account_id', 'fullname', 'submitted_at', 'rico_status', 
        'customer_name', 'account_type', 'identifier', 'status', 'last_action_owner', 'updated_at'

        // Add other fields if needed
    ];
 

}
