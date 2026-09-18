<?php

namespace Tests\Feature;

use App\Filament\Resources\Roles\Pages\ManageRoles;
use App\Models\User;
use App\Support\AccessArea;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Panelden rol oluşturma/düzenleme akışı. Yetki kutuları izin kayıtlarının
 * id'siyle çalışmalıdır; izin adı gönderilirse pivot kaydı veritabanı
 * hatasına düşer.
 */
class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsSuperAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Süper Admin'));

        $this->actingAs($user);

        return $user;
    }

    public function test_role_can_be_created_with_area_permissions_from_the_panel(): void
    {
        $this->actingAsSuperAdmin();

        $clients = Permission::findOrCreate(AccessArea::CLIENTS);
        $accounting = Permission::findOrCreate(AccessArea::ACCOUNTING);
        Permission::findOrCreate(AccessArea::SETTINGS);

        Livewire::test(ManageRoles::class)
            ->callAction(TestAction::make('create'), [
                'name' => 'Muhasebe Sorumlusu',
                'permissions' => [$clients->id, $accounting->id],
            ])
            ->assertHasNoActionErrors();

        $role = Role::query()->where('name', 'Muhasebe Sorumlusu')->firstOrFail();

        $this->assertEqualsCanonicalizing(
            [AccessArea::CLIENTS, AccessArea::ACCOUNTING],
            $role->permissions->pluck('name')->all(),
        );
    }

    public function test_role_permissions_can_be_changed_from_the_panel(): void
    {
        $this->actingAsSuperAdmin();

        $clients = Permission::findOrCreate(AccessArea::CLIENTS);
        $tasks = Permission::findOrCreate(AccessArea::TASKS);

        $role = Role::create(['name' => 'Ekip Üyesi']);
        $role->givePermissionTo($clients);

        Livewire::test(ManageRoles::class)
            ->callAction(TestAction::make('edit')->table($role), [
                'name' => 'Ekip Üyesi',
                'permissions' => [$tasks->id],
            ])
            ->assertHasNoActionErrors();

        $this->assertSame([AccessArea::TASKS], $role->fresh()->permissions->pluck('name')->all());
    }

    public function test_created_role_actually_gates_panel_access(): void
    {
        $this->actingAsSuperAdmin();

        $tasks = Permission::findOrCreate(AccessArea::TASKS);
        Permission::findOrCreate(AccessArea::CLIENTS);

        Livewire::test(ManageRoles::class)
            ->callAction(TestAction::make('create'), [
                'name' => 'Görev Sorumlusu',
                'permissions' => [$tasks->id],
            ])
            ->assertHasNoActionErrors();

        $member = User::factory()->create();
        $member->assignRole('Görev Sorumlusu');

        $this->actingAs($member);
        $this->get('admin/tasks')->assertOk();
        $this->get('admin/client-debts')->assertForbidden();
    }
}
