<?php
// Model: app/Models/Collection.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = [
        'customer_id', 'agent_id', 'amount', 'collected_at', 'notes'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);

    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}