<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Customer extends Model
{
    protected $fillable = ['name','email','phone','address','outstanding_balance'];
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class,'auditable');
    }
}
