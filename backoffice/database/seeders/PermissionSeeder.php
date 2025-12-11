<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define modules and their CRUD permissions
        $modules = [
            'users' => 'Users',
            'roles' => 'Roles',
            'packages' => 'Packages',
            'payments' => 'Payments',
            'refunds' => 'Refunds',
            'support-tickets' => 'Support Tickets',
            'logs' => 'Logs',
            'analytics' => 'Analytics',
        ];

        $actions = [
            'read' => 'Read',
            'write' => 'Write',
            'update' => 'Update',
            'delete' => 'Delete',
        ];

        // Create permissions for each module
        foreach ($modules as $module => $moduleName) {
            foreach ($actions as $action => $actionName) {
                $permissionName = "{$module}.{$action}";
                Permission::firstOrCreate(
                    ['name' => $permissionName],
                    ['guard_name' => 'web']
                );
            }
        }

        $this->command->info('CRUD permissions created for all modules.');
    }
}

