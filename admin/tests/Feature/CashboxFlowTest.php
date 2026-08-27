<?php

namespace Tests\Feature;

use App\Models\CashboxTransaction;
use App\Models\Client;
use App\Models\ClientDebt;
use App\Models\ClientPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Müvekkil, borçlandırma, ödeme ve ana kasa arasındaki otomatik
 * muhasebeleştirme akışını uçtan uca doğrular.
 */
class CashboxFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_type_debt_immediately_posts_to_cashbox(): void
    {
        $client = Client::factory()->create();

        $debt = ClientDebt::query()->create([
            'client_id' => $client->id,
            'type' => ClientDebt::TYPE_EXPENSE,
            'title' => 'Harç Masrafı',
            'amount' => 1500,
            'status' => 'bekliyor',
        ]);

        $this->assertNotNull($debt->fresh()->cashbox_transaction_id);
        $this->assertSame(-1500.0, CashboxTransaction::currentBalance());

        $transaction = $debt->fresh()->cashboxTransaction;
        $this->assertSame(CashboxTransaction::TYPE_EXPENSE, $transaction->type);
        $this->assertSame('client_debt', $transaction->source);
    }

    public function test_fee_type_debt_does_not_post_to_cashbox(): void
    {
        $client = Client::factory()->create();

        $debt = ClientDebt::query()->create([
            'client_id' => $client->id,
            'type' => ClientDebt::TYPE_FEE,
            'title' => 'Vekalet Ücreti',
            'amount' => 10000,
            'status' => 'bekliyor',
        ]);

        $this->assertNull($debt->fresh()->cashbox_transaction_id);
        $this->assertSame(0.0, CashboxTransaction::currentBalance());
    }

    public function test_immediate_cash_payment_posts_to_cashbox_right_away(): void
    {
        $client = Client::factory()->create();

        $payment = ClientPayment::query()->create([
            'client_id' => $client->id,
            'method' => 'nakit',
            'amount' => 5000,
            'payment_date' => now(),
            'is_postdated' => false,
        ]);

        $payment->refresh();

        $this->assertSame(ClientPayment::STATUS_COLLECTED, $payment->status);
        $this->assertNotNull($payment->cashbox_transaction_id);
        $this->assertNotNull($payment->collected_at);
        $this->assertSame(5000.0, CashboxTransaction::currentBalance());
    }

    public function test_postdated_check_does_not_post_until_manually_collected(): void
    {
        $client = Client::factory()->create();

        $debt = ClientDebt::query()->create([
            'client_id' => $client->id,
            'type' => ClientDebt::TYPE_FEE,
            'title' => 'Vekalet Ücreti',
            'amount' => 8000,
            'status' => 'bekliyor',
        ]);

        $payment = ClientPayment::query()->create([
            'client_id' => $client->id,
            'client_debt_id' => $debt->id,
            'method' => 'cek',
            'amount' => 8000,
            'payment_date' => now()->addDays(30),
            'is_postdated' => true,
            'instrument_no' => 'CEK-001',
        ]);

        $payment->refresh();

        // Vadesi gelmemiş çek: kasaya henüz işlenmemeli.
        $this->assertSame(ClientPayment::STATUS_PENDING, $payment->status);
        $this->assertNull($payment->cashbox_transaction_id);
        $this->assertSame(0.0, CashboxTransaction::currentBalance());
        $this->assertSame('bekliyor', $debt->fresh()->status);

        // Vadesinde tahsil edildiğinde kasaya gelir olarak işlenmeli ve
        // ilişkili borçlandırma "ödendi" olarak güncellenmelidir.
        $payment->markCollected();
        $payment->refresh();

        $this->assertSame(ClientPayment::STATUS_COLLECTED, $payment->status);
        $this->assertNotNull($payment->cashbox_transaction_id);
        $this->assertSame(8000.0, CashboxTransaction::currentBalance());
        $this->assertSame('odendi', $debt->fresh()->status);
    }

    public function test_bounced_check_never_posts_to_cashbox(): void
    {
        $client = Client::factory()->create();

        $payment = ClientPayment::query()->create([
            'client_id' => $client->id,
            'method' => 'cek',
            'amount' => 3000,
            'payment_date' => now()->addDays(10),
            'is_postdated' => true,
        ]);

        $payment->markBounced();
        $payment->refresh();

        $this->assertSame(ClientPayment::STATUS_BOUNCED, $payment->status);
        $this->assertNull($payment->cashbox_transaction_id);
        $this->assertSame(0.0, CashboxTransaction::currentBalance());
    }

    public function test_manual_deposit_and_withdrawal_affect_balance_correctly(): void
    {
        CashboxTransaction::query()->create([
            'type' => CashboxTransaction::TYPE_DEPOSIT,
            'amount' => 20000,
            'date' => now(),
            'source' => 'manuel',
        ]);

        CashboxTransaction::query()->create([
            'type' => CashboxTransaction::TYPE_WITHDRAWAL,
            'amount' => 7000,
            'date' => now(),
            'source' => 'manuel',
        ]);

        $this->assertSame(13000.0, CashboxTransaction::currentBalance());
    }

    public function test_client_balance_helpers_reflect_debts_and_collections(): void
    {
        $client = Client::factory()->create();

        ClientDebt::query()->create([
            'client_id' => $client->id,
            'type' => ClientDebt::TYPE_FEE,
            'title' => 'Vekalet Ücreti',
            'amount' => 10000,
            'status' => 'bekliyor',
        ]);

        ClientPayment::query()->create([
            'client_id' => $client->id,
            'method' => 'nakit',
            'amount' => 4000,
            'payment_date' => now(),
            'is_postdated' => false,
        ]);

        $client->refresh();

        $this->assertSame(10000.0, $client->totalDebt());
        $this->assertSame(4000.0, $client->totalCollected());
        $this->assertSame(6000.0, $client->balance());
    }
}
