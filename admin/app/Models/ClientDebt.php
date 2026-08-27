<?php

namespace App\Models;

use Database\Factories\ClientDebtFactory;
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

    public function isOverdue(): bool
    {
        return $this->status === 'bekliyor'
            && $this->due_date !== null
            && $this->due_date->isPast();
    }
}
