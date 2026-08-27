<?php

namespace App\Models;

use Database\Factories\ClientPaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ClientPayment extends Model
{
    /** @use HasFactory<ClientPaymentFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'bekliyor';

    public const STATUS_COLLECTED = 'tahsil_edildi';

    public const STATUS_BOUNCED = 'karsiliksiz';

    public const STATUS_CANCELLED = 'iptal';

    public const METHODS = [
        'nakit' => 'Nakit',
        'havale' => 'Havale / EFT',
        'kredi_karti' => 'Kredi Kartı',
        'cek' => 'Çek',
        'senet' => 'Senet',
    ];

    protected $fillable = [
        'client_id',
        'client_debt_id',
        'method',
        'amount',
        'payment_date',
        'is_postdated',
        'status',
        'instrument_no',
        'bank_name',
        'description',
        'cashbox_transaction_id',
        'collected_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'is_postdated' => 'boolean',
            'collected_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $payment): void {
            if (blank($payment->created_by) && Auth::check()) {
                $payment->created_by = Auth::id();
            }

            if (blank($payment->status)) {
                $payment->status = $payment->is_postdated
                    ? self::STATUS_PENDING
                    : self::STATUS_COLLECTED;
            }
        });

        static::created(function (self $payment): void {
            if ($payment->status === self::STATUS_COLLECTED && blank($payment->cashbox_transaction_id)) {
                $payment->postToCashbox();
            }
        });
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<ClientDebt, $this>
     */
    public function debt(): BelongsTo
    {
        return $this->belongsTo(ClientDebt::class, 'client_debt_id');
    }

    /**
     * @return BelongsTo<CashboxTransaction, $this>
     */
    public function cashboxTransaction(): BelongsTo
    {
        return $this->belongsTo(CashboxTransaction::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }

    protected function postToCashbox(): void
    {
        $transaction = CashboxTransaction::query()->create([
            'type' => CashboxTransaction::TYPE_INCOME,
            'amount' => $this->amount,
            'category' => 'Müvekkil Tahsilatı',
            'description' => "{$this->client->fullName()} — {$this->methodLabel()} tahsilatı",
            'date' => now()->toDateString(),
            'client_id' => $this->client_id,
            'source' => 'client_payment',
            'created_by' => $this->created_by,
        ]);

        $this->cashbox_transaction_id = $transaction->id;
        $this->collected_at = now();
        $this->saveQuietly();
    }

    /**
     * Vadeli çek/senedin tahsil edildiğini işaretler ve ana kasaya gelir olarak işler.
     */
    public function markCollected(): void
    {
        if ($this->status === self::STATUS_COLLECTED) {
            return;
        }

        $this->status = self::STATUS_COLLECTED;
        $this->saveQuietly();
        $this->postToCashbox();

        if ($this->client_debt_id) {
            $this->debt?->update(['status' => 'odendi']);
        }
    }

    /**
     * Karşılıksız çıkan çek/senedi işaretler; kasaya herhangi bir kayıt düşmez.
     */
    public function markBounced(): void
    {
        $this->update(['status' => self::STATUS_BOUNCED]);
    }
}
