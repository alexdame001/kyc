<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        // 'name', 'email', 'password', 'role',
         'name', 'email', 'role', 'location', 'password', 'phone', 'must_change_password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function location()
{
    return $this->belongsTo(Location::class);
}

}
