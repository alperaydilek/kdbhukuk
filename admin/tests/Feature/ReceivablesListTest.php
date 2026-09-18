<?php

namespace Tests\Feature;

use App\Filament\Resources\ClientDebts\Pages\ManageClientDebts;
use App\Models\CashboxTransaction;
use App\Models\Client;
use App\Models\ClientDebt;
use App\Models\ClientPayment;
use App\Models\User;
use App\Support\AccessArea;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Alacak listesi: müvekkil borçlandırmalarının vadeye göre sıralanması,
 * gecikmiş alacakların işaretlenmesi ve "Ödeme Alındı" akışı.
 */
class ReceivablesListTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsSuperAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Süper Admin'));

        $this->actingAs($user);

        return $user;
    }

    public function test_receivables_page_lists_outstanding_debts(): void
    {
        $this->actingAsSuperAdmin();

        $client = Client::factory()->create(['first_name' => 'Ayşe', 'last_name' => 'Yılmaz']);

        ClientDebt::factory()->create([
            'client_id' => $client->id,
            'title' => 'Vekalet Ücreti',
            'amount' => 12000,
            'due_date' => now()->addDays(10),
        ]);

        $response = $this->get('admin/client-debts');

        $response->assertOk();
        $response->assertSee('Vekalet Ücreti');
        $response->assertSee('Ayşe Yılmaz');
    }

    public function test_debts_are_sorted_from_nearest_due_date_to_furthest(): void
    {
        $this->actingAsSuperAdmin();

        $client = Client::factory()->create();

        $furthest = ClientDebt::factory()->create([
            'client_id' => $client->id,
            'title' => 'Uzak vadeli alacak',
            'due_date' => now()->addDays(60),
        ]);

        $overdue = ClientDebt::factory()->create([
            'client_id' => $client->id,
            'title' => 'Gecikmiş alacak',
            'due_date' => now()->subDays(5),
        ]);

        $nearest = ClientDebt::factory()->create([
            'client_id' => $client->id,
            'title' => 'Yakın vadeli alacak',
            'due_date' => now()->addDays(3),
        ]);

        Livewire::test(ManageClientDebts::class)
            ->assertCanSeeTableRecords([$overdue, $nearest, $furthest], inOrder: true);
    }

    public function test_overdue_debt_is_flagged_with_day_count(): void
    {
        $debt = ClientDebt::factory()->create(['due_date' => now()->subDays(7)]);

        $this->assertTrue($debt->isOverdue());
        $this->assertSame(7, $debt->daysOverdue());
    }

    public function test_future_dated_debt_is_not_flagged_as_overdue(): void
    {
        $debt = ClientDebt::factory()->create(['due_date' => now()->addDays(7)]);

        $this->assertFalse($debt->isOverdue());
        $this->assertSame(0, $debt->daysOverdue());
    }

    public function test_marking_payment_received_collects_debt_and_posts_income_to_cashbox(): void
    {
        $client = Client::factory()->create();

        $debt = ClientDebt::factory()->create([
            'client_id' => $client->id,
            'type' => ClientDebt::TYPE_FEE,
            'amount' => 7500,
            'due_date' => now()->subDays(2),
        ]);

        $this->assertSame(0.0, CashboxTransaction::currentBalance());

        $payment = $debt->markPaid('havale');

        $this->assertNotNull($payment);
        $this->assertSame(ClientDebt::STATUS_PAID, $debt->fresh()->status);

        $payment->refresh();
        $this->assertSame(ClientPayment::STATUS_COLLECTED, $payment->status);
        $this->assertSame($client->id, $payment->client_id);
        $this->assertSame($debt->id, $payment->client_debt_id);
        $this->assertSame('havale', $payment->method);
        $this->assertFalse($payment->is_postdated);
        $this->assertNotNull($payment->cashbox_transaction_id);

        $this->assertSame(7500.0, CashboxTransaction::currentBalance());
    }

    public function test_already_paid_debt_is_not_collected_twice(): void
    {
        $debt = ClientDebt::factory()->create(['amount' => 4000]);

        $debt->markPaid();
        $this->assertNull($debt->fresh()->markPaid());

        $this->assertSame(1, ClientPayment::query()->where('client_debt_id', $debt->id)->count());
        $this->assertSame(4000.0, CashboxTransaction::currentBalance());
    }

    public function test_payment_received_table_action_collects_the_debt(): void
    {
        $this->actingAsSuperAdmin();

        $debt = ClientDebt::factory()->create([
            'amount' => 5000,
            'due_date' => now()->subDay(),
        ]);

        Livewire::test(ManageClientDebts::class)
            ->callAction(TestAction::make('markPaid')->table($debt), [
                'method' => 'nakit',
                'payment_date' => now()->toDateString(),
            ])
            ->assertHasNoActionErrors();

        $this->assertSame(ClientDebt::STATUS_PAID, $debt->fresh()->status);
        $this->assertSame(5000.0, CashboxTransaction::currentBalance());
    }

    public function test_payment_received_action_is_hidden_for_already_paid_debts(): void
    {
        $this->actingAsSuperAdmin();

        $paid = ClientDebt::factory()->create(['status' => ClientDebt::STATUS_PAID]);

        Livewire::test(ManageClientDebts::class)
            ->filterTable('status', ClientDebt::STATUS_PAID)
            ->assertActionHidden(TestAction::make('markPaid')->table($paid));
    }

    public function test_user_without_client_permission_cannot_open_receivables_page(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Görev Sorumlusu']);
        $role->givePermissionTo(Permission::findOrCreate(AccessArea::TASKS));
        $user->assignRole($role);

        $this->actingAs($user)->get('admin/client-debts')->assertForbidden();
    }
}
