<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'super_admin', 'description' => 'Platform architects (developers)'],
            ['name' => 'admin',       'description' => 'IBEDC billing & payment team'],
            ['name' => 'agency',      'description' => 'External debt recovery agency'],
            ['name' => 'agent',       'description' => 'Individual field agent'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
