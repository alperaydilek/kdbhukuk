<?php

namespace App\Models;

use Database\Factories\ClientDebtFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class ClientDebt extends Model
{
    /** @use HasFactory<ClientDebtFactory> */
    use HasFactory;

    public const TYPE_FEE = 'ucret';

    public const TYPE_EXPENSE = 'masraf';

    public const STATUS_PENDING = 'bekliyor';

    public const STATUS_PAID = 'odendi';

    public const STATUS_CANCELLED = 'iptal';

    public const STATUSES = [
        self::STATUS_PENDING => 'Bekliyor',
        self::STATUS_PAID => 'Ödendi',
        self::STATUS_CANCELLED => 'İptal',
    ];

    protected $fillable = [
        'client_id',
        'type',
        'title',
        'description',
        'amount',
        'due_date',
        'status',
        'cashbox_transaction_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $debt): void {
            if (blank($debt->created_by) && Auth::check()) {
                $debt->created_by = Auth::id();
            }
        });

        static::created(function (self $debt): void {
            // "Masraf" tipi borçlandırma: avukat müvekkil adına şimdi ödeme yapmıştır,
            // bu tutar anında ana kasadan gider olarak düşer.
            if ($debt->type === self::TYPE_EXPENSE && blank($debt->cashbox_transaction_id)) {
                $transaction = CashboxTransaction::query()->create([
                    'type' => CashboxTransaction::TYPE_EXPENSE,
                    'amount' => $debt->amount,
                    'category' => 'Müvekkil Masrafı',
                    'description' => "{$debt->client->fullName()} — {$debt->title}",
                    'date' => now()->toDateString(),
                    'client_id' => $debt->client_id,
                    'source' => 'client_debt',
                    'created_by' => $debt->created_by,
                ]);

                $debt->cashbox_transaction_id = $transaction->id;
                $debt->saveQuietly();
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

    /**
     * @return HasMany<ClientPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(ClientPayment::class);
    }

    /**
     * Tahsil edilmeyi bekleyen (iptal veya ödenmiş olmayan) alacaklar.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    /**
     * Vadesi geçmiş alacağın kaç gün geciktiği; gecikme yoksa 0.
     */
    public function daysOverdue(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        return (int) $this->due_date->diffInDays(now()->startOfDay());
    }

    /**
     * Alacağın tahsil edildiğini işaretler: müvekkil adına anında tahsil edilmiş
     * bir ödeme kaydı oluşturur (ana kasaya gelir olarak düşer) ve borcu
     * "ödendi" durumuna geçirir. Zaten ödenmiş bir alacak tekrar işlenmez.
     */
    public function markPaid(string $method = 'nakit', ?string $paymentDate = null): ?ClientPayment
    {
        if ($this->status === self::STATUS_PAID) {
            return null;
        }

        $payment = $this->payments()->create([
            'client_id' => $this->client_id,
            'method' => $method,
            'amount' => $this->amount,
            'payment_date' => $paymentDate ?? now()->toDateString(),
            'is_postdated' => false,
            'description' => "{$this->title} — alacak tahsilatı",
        ]);

        $this->update(['status' => self::STATUS_PAID]);

        return $payment;
    }
}
