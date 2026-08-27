<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\AccessArea;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (AccessArea::all() as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::query()->firstOrCreate(['name' => 'Süper Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(AccessArea::all());

        $teamMember = Role::query()->firstOrCreate(['name' => 'Ekip Üyesi', 'guard_name' => 'web']);
        $teamMember->syncPermissions([
            AccessArea::CLIENTS,
            AccessArea::ACCOUNTING,
            AccessArea::TASKS,
        ]);

        // İlk oluşturulan panel kullanıcısına Süper Admin rolünü ata.
        $firstUser = User::query()->oldest('id')->first();
        $firstUser?->assignRole($superAdmin);
    }
}
