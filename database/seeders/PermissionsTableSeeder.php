<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $perms = [
            // User management
            'user.create',
            'user.assign_roles',
            // Agency & Agent
            'agency.create',
            'agency.view',
            'agency.update',
            'agent.create',
            'agent.view',
            'agent.update',
            // Customers & Debts
            'customer.view',
            'debt.view',
            'debt.update',
            // Payments & Reconciliation
            'payment.record',
            'reconciliation.view',
            // Targets & Reports
            'target.view',
            'target.update',
            'report.view',
            // Negotiations
            'negotiation.create',
            'negotiation.view',
        ];

        foreach ($perms as $name) {
            Permission::updateOrCreate(['name' => $name], ['description' => null]);
        }
    }
}
