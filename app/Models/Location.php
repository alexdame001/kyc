<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['region', 'name'];
    public $timestamps = false;

    // relation to users
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
