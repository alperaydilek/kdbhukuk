<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\LegalPage;
use App\Models\Page;
use App\Models\Service;
use App\Models\ServiceFaq;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_settings_endpoint_returns_data(): void
    {
        Setting::putGroup('site', ['phone' => '0212 000 00 00']);

        $this->getJson('/api/site-settings')
            ->assertOk()
            ->assertJsonPath('data.phone', '0212 000 00 00');
    }

    public function test_page_endpoint_returns_page_data(): void
    {
        Page::query()->create(['slug' => 'home', 'title' => 'Ana Sayfa', 'data' => ['hero_title' => 'Test Başlık']]);

        $this->getJson('/api/pages/home')
            ->assertOk()
            ->assertJsonPath('data.fields.hero_title', 'Test Başlık');
    }

    public function test_page_endpoint_returns_404_for_missing_slug(): void
    {
        $this->getJson('/api/pages/does-not-exist')->assertNotFound();
    }

    public function test_legal_page_endpoint_returns_content(): void
    {
        LegalPage::query()->create(['slug' => 'kvkk', 'title' => 'KVKK', 'content' => '<p>İçerik</p>']);

        $this->getJson('/api/legal-pages/kvkk')
            ->assertOk()
            ->assertJsonPath('data.title', 'KVKK');
    }

    public function test_services_index_only_returns_published_ordered_services(): void
    {
        Service::factory()->create(['title' => 'B Hizmet', 'order' => 2, 'is_published' => true]);
        Service::factory()->create(['title' => 'A Hizmet', 'order' => 1, 'is_published' => true]);
        Service::factory()->create(['title' => 'Gizli Hizmet', 'order' => 3, 'is_published' => false]);

        $response = $this->getJson('/api/services')->assertOk();

        $titles = collect($response->json('data'))->pluck('title');

        $this->assertSame(['A Hizmet', 'B Hizmet'], $titles->all());
    }

    public function test_service_show_endpoint_includes_faqs(): void
    {
        $service = Service::factory()->create(['slug' => 'gayrimenkul-hukuku', 'is_published' => true]);
        ServiceFaq::factory()->create(['service_id' => $service->id, 'question' => 'Soru?', 'answer' => 'Cevap.']);

        $this->getJson('/api/services/gayrimenkul-hukuku')
            ->assertOk()
            ->assertJsonPath('data.faqs.0.question', 'Soru?');
    }

    public function test_blog_index_filters_by_category(): void
    {
        $category = BlogCategory::factory()->create(['name' => 'Gayrimenkul']);
        $otherCategory = BlogCategory::factory()->create(['name' => 'Aile Hukuku']);

        BlogPost::factory()->create(['blog_category_id' => $category->id, 'status' => 'yayinda', 'title' => 'Gayrimenkul Yazısı']);
        BlogPost::factory()->create(['blog_category_id' => $otherCategory->id, 'status' => 'yayinda', 'title' => 'Aile Yazısı']);
        BlogPost::factory()->create(['blog_category_id' => $category->id, 'status' => 'taslak', 'title' => 'Taslak Yazı']);

        $response = $this->getJson('/api/blog?category=gayrimenkul')->assertOk();

        $titles = collect($response->json('data'))->pluck('title');

        $this->assertSame(['Gayrimenkul Yazısı'], $titles->all());
    }

    public function test_blog_show_endpoint_returns_full_content(): void
    {
        BlogPost::factory()->create(['slug' => 'test-yazi', 'status' => 'yayinda', 'content' => '<p>Tam içerik</p>']);

        $this->getJson('/api/blog/test-yazi')
            ->assertOk()
            ->assertJsonPath('data.content', '<p>Tam içerik</p>');
    }

    public function test_contact_submission_can_be_created(): void
    {
        $payload = [
            'name' => 'Ahmet Yılmaz',
            'phone' => '05551234567',
            'email' => 'ahmet@example.com',
            'subject' => 'gayrimenkul',
            'message' => 'Bu mesaj en az yirmi karakter uzunluğunda bir test mesajıdır.',
            'kvkk_consent' => true,
        ];

        $this->postJson('/api/contact', $payload)->assertCreated();

        $this->assertDatabaseHas('contact_submissions', ['email' => 'ahmet@example.com']);
    }

    public function test_contact_submission_requires_kvkk_consent(): void
    {
        $payload = [
            'name' => 'Ahmet Yılmaz',
            'phone' => '05551234567',
            'email' => 'ahmet@example.com',
            'message' => 'Bu mesaj en az yirmi karakter uzunluğunda bir test mesajıdır.',
            'kvkk_consent' => false,
        ];

        $this->postJson('/api/contact', $payload)->assertUnprocessable();
    }

    public function test_appointment_request_can_be_created(): void
    {
        $payload = [
            'name' => 'Ayşe Kaya',
            'phone' => '05559876543',
            'email' => 'ayse@example.com',
        ];

        $this->postJson('/api/appointments', $payload)->assertCreated();

        $this->assertDatabaseHas('appointment_requests', ['email' => 'ayse@example.com', 'status' => 'bekliyor']);
    }
}
