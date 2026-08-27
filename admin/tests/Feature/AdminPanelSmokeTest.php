<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Support\AccessArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsSuperAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('Süper Admin');
        $user->assignRole($role);

        $this->actingAs($user);

        return $user;
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function pageUrls(): array
    {
        return [
            ['admin/services'],
            ['admin/blog-posts'],
            ['admin/blog-categories'],
            ['admin/legal-pages'],
            ['admin/contact-submissions'],
            ['admin/appointment-requests'],
            ['admin/clients'],
            ['admin/cashbox-transactions'],
            ['admin/planned-payments'],
            ['admin/tasks'],
            ['admin/task-board'],
            ['admin/users'],
            ['admin/roles'],
            ['admin/mail-templates'],
            ['admin/sms-templates'],
            ['admin/communication-logs'],
            ['admin/site-settings'],
            ['admin/mail-settings'],
            ['admin/sms-settings'],
            ['admin/ai-settings'],
            ['admin/home-page-content'],
            ['admin/about-page-content'],
            ['admin/contact-page-content'],
            ['admin/profile'],
            ['admin'],
        ];
    }

    #[DataProvider('pageUrls')]
    public function test_authenticated_admin_pages_load_successfully(string $url): void
    {
        $this->actingAsSuperAdmin();

        $this->get($url)->assertOk();
    }

    public function test_client_view_page_loads_with_relation_managers(): void
    {
        $this->actingAsSuperAdmin();
        $client = Client::factory()->create();

        $this->get("admin/clients/{$client->id}")->assertOk();
    }

    public function test_client_view_page_has_debt_and_payment_quick_actions(): void
    {
        $this->actingAsSuperAdmin();
        $client = Client::factory()->create();

        $this->get("admin/clients/{$client->id}")
            ->assertOk()
            ->assertSee('Borçlandır')
            ->assertSee('Ödeme Ekle');
    }

    public function test_profile_page_offers_optional_app_authentication_setup(): void
    {
        $user = User::factory()->create();

        // 2FA varsayılan olarak pasif: kullanıcı henüz bir gizli anahtar
        // kaydetmemiş olmalı.
        $this->assertNull($user->getAppAuthenticationSecret());

        $this->actingAs($user)
            ->get('admin/profile')
            ->assertOk();
    }

    public function test_users_without_permission_are_forbidden_from_restricted_areas(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('admin/clients')->assertForbidden();
        $this->actingAs($user)->get('admin/cashbox-transactions')->assertForbidden();
    }

    public function test_role_with_specific_permission_grants_access_to_only_that_area(): void
    {
        $user = User::factory()->create();
        Permission::findOrCreate(AccessArea::TASKS);
        $role = Role::findOrCreate('Sadece Görevler');
        $role->givePermissionTo(AccessArea::TASKS);
        $user->assignRole($role);

        $this->actingAs($user)->get('admin/tasks')->assertOk();
        $this->actingAs($user)->get('admin/clients')->assertForbidden();
    }
}
