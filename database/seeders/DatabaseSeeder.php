<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application’s database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Create roles (super_admin, admin, agency, agent)
            RolesTableSeeder::class,

            // 2. Create all fine-grained permissions
            PermissionsTableSeeder::class,

            // 3. Map permissions → roles per your hierarchy
            RolePermissionTableSeeder::class,

            // 4. Bootstrap your first Super Admin user
            SuperAdminUserSeeder::class,
        ]);
    }
}
