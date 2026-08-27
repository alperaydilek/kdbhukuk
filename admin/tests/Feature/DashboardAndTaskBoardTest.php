<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardAndTaskBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_widgets_and_nav_order(): void
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('Süper Admin');
        $user->assignRole($role);

        $response = $this->actingAs($user)->get('admin');
        $response->assertOk();
        $response->assertSee('Yaklaşan Gelecek Ödemeler');
        $response->assertSee('Yaklaşan Gidecek Ödemeler');
        $response->assertSee('Görevlerim');

        $content = $response->getContent();
        $posMuvekkiller = strpos($content, 'Müvekkiller');
        $posAyarlar = strpos($content, 'Ayarlar');
        $this->assertTrue($posMuvekkiller !== false && $posAyarlar !== false && $posMuvekkiller < $posAyarlar);
    }

    public function test_task_board_renders_columns(): void
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('Süper Admin');
        $user->assignRole($role);
        $assignee = User::factory()->create(['name' => 'Ayşe Yılmaz']);

        Task::query()->create([
            'title' => 'Dava dosyasını incele',
            'assigned_to' => $assignee->id,
            'assigned_by' => $user->id,
            'status' => 'yapilacak',
            'priority' => 'yuksek',
        ]);

        $response = $this->actingAs($user)->get('admin/task-board');
        $response->assertOk();
        $response->assertSee('Dava dosyasını incele');
        $response->assertSee('Ayşe Yılmaz');
        $response->assertSee('Yapılacak');
        $response->assertSee('Yapılıyor');
    }

    public function test_completed_tasks_disappear_from_board_but_stay_in_task_list(): void
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('Süper Admin');
        $user->assignRole($role);

        $openTask = Task::query()->create([
            'title' => 'Açık görev',
            'assigned_to' => $user->id,
            'status' => Task::STATUS_TODO,
        ]);

        $doneTask = Task::query()->create([
            'title' => 'Tamamlanmış görev',
            'assigned_to' => $user->id,
            'status' => Task::STATUS_DONE,
        ]);

        // Pano: yalnızca açık görev görünür; tamamlanan görevin kartı/başlığı
        // panoda yer almaz (yalnızca kart üzerindeki "Tamamlandı" işaretleme
        // butonu metni sayfada bulunabilir, bu beklenen bir durumdur).
        $boardResponse = $this->actingAs($user)->get('admin/task-board');
        $boardResponse->assertOk();
        $boardResponse->assertSee('Açık görev');
        $boardResponse->assertDontSee('Tamamlanmış görev');

        // Tüm Görevler listesi: tamamlanan görev hâlâ "Tamamlandı" olarak görünür.
        $listResponse = $this->actingAs($user)->get('admin/tasks');
        $listResponse->assertOk();
        $listResponse->assertSee('Açık görev');
        $listResponse->assertSee('Tamamlanmış görev');
        $listResponse->assertSeeText('Tamamlandı');
    }

    public function test_assigning_task_sends_database_notification(): void
    {
        $assigner = User::factory()->create();
        $assignee = User::factory()->create();

        Task::query()->create([
            'title' => 'Sözleşmeyi gözden geçir',
            'assigned_to' => $assignee->id,
            'assigned_by' => $assigner->id,
            'status' => 'yapilacak',
        ]);

        $this->assertSame(1, $assignee->fresh()->unreadNotifications()->count());
    }
}
