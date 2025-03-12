<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions
        $permissions = [
            // Product permissions
            'create product',
            'view product',
            'update product',
            'delete product',

            // Stock permissions
            'view stock',
            'update stock',
            'transfer stock',

            // Warehouse permissions
            'create warehouse',
            'view warehouse',
            'update warehouse',
            'delete warehouse',

            // User & Role Management
            'view users',
            'assign roles',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);

        // Assign all permissions to Admin
        $adminRole->syncPermissions($permissions);

        // Assign specific permissions to Staff
        $staffRole->syncPermissions([
            'view product',
            'view stock',
            'transfer stock',
            'view warehouse',
        ]);

        echo "Roles and permissions seeded successfully!\n";
    }
}
