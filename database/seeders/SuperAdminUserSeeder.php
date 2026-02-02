<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminUserSeeder extends Seeder
{
    public function run()
    {
        // Adjust these to your preferred email/password (or read from env)
        $email    = env('SUPER_ADMIN_EMAIL', 'dev@ibedc.local');
        $password = env('SUPER_ADMIN_PASSWORD', 'secret123');

        // Create the user if it doesn't exist
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make($password),
                'role_id'  => $role->id,
                // role_name backfilled by booted() saving hook
            ]
        );

        // Attach the super_admin role
        $role = Role::where('name', 'super_admin')->first();
        if ($role && ! $user->roles->contains($role->id)) {
            $user->roles()->attach($role->id);
        }
    }
}
