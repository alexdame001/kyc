<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class Agency extends Model
{
    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'email_verified_at',
        'verification_token',
        'token_expires_at',
        'num_agents',
        'agency_code',
        'status',
        'date_confirmed',
        'locations',
        'created_by',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'token_expires_at'  => 'datetime',
        'num_agents'        => 'integer',
        'date_confirmed'    => 'date',
        'locations'         => AsArrayObject::class,
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}