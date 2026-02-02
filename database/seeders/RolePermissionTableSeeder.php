<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionTableSeeder extends Seeder
{
    public function run()
    {
        // Fetch roles
        $superAdmin = Role::where('name', 'super_admin')->first();
        $admin      = Role::where('name', 'admin')->first();
        $agency     = Role::where('name', 'agency')->first();
        $agent      = Role::where('name', 'agent')->first();

        // Fetch all permissions
        $allPermissions = Permission::all();

        // 1. Super Admin → only user management (they rarely use the app)
        $superAdmin->permissions()->sync(
            Permission::whereIn('name', ['user.create', 'user.assign_roles'])->pluck('id')
        );

        // 2. Admin → all permissions
        $admin->permissions()->sync($allPermissions->pluck('id'));

        // 3. Agency → limited to view-only for their scope
        $agencyPerms = [
            'agency.view',
            'agent.view',
            'customer.view',
            'debt.view',
            'report.view',
        ];
        $agency->permissions()->sync(
            Permission::whereIn('name', $agencyPerms)->pluck('id')
        );

        // 4. Agent → very limited
        $agentPerms = [
            'customer.view',
            'debt.view',
            'payment.record',
            'negotiation.create',
            'negotiation.view',
            'target.view',
        ];
        $agent->permissions()->sync(
            Permission::whereIn('name', $agentPerms)->pluck('id')
        );
    }
}
