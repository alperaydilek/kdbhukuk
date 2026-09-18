<?php

namespace Tests\Feature;

use App\Filament\Pages\ContactPageContent;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Ofis konumu panelden harita üzerinde işaretlenir ve iletişim sayfasının
 * API çıktısına koordinat olarak yansır.
 */
class OfficeLocationTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsSuperAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Süper Admin'));

        $this->actingAs($user);

        return $user;
    }

    public function test_office_location_can_be_saved_from_the_map_field(): void
    {
        $this->actingAsSuperAdmin();

        Page::query()->create(['slug' => 'iletisim', 'title' => 'İletişim', 'data' => []]);

        Livewire::test(ContactPageContent::class)
            ->fillForm([
                'intro_text' => 'Bize ulaşın.',
                'map_location' => ['lat' => 41.0602, 'lng' => 28.9877],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $data = Page::query()->firstWhere('slug', 'iletisim')->data;

        $this->assertSame(41.0602, $data['map_location']['lat']);
        $this->assertSame(28.9877, $data['map_location']['lng']);
    }

    public function test_saved_location_is_exposed_through_the_public_page_api(): void
    {
        Page::query()->create([
            'slug' => 'iletisim',
            'title' => 'İletişim',
            'data' => ['map_location' => ['lat' => 41.0602, 'lng' => 28.9877]],
        ]);

        $this->getJson('api/pages/iletisim')
            ->assertOk()
            ->assertJsonPath('data.fields.map_location.lat', 41.0602)
            ->assertJsonPath('data.fields.map_location.lng', 28.9877);
    }

    public function test_contact_content_page_renders_the_map_field(): void
    {
        $this->actingAsSuperAdmin();

        $this->get('admin/contact-page-content')
            ->assertOk()
            ->assertSee('Ofis Konumu')
            ->assertSee('Harita Üzerinde Konum');
    }
}
