<?php

namespace App\Models;

use Database\Factories\PlannedPaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class PlannedPayment extends Model
{
    /** @use HasFactory<PlannedPaymentFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'bekliyor';

    public const STATUS_PAID = 'odendi';

    public const STATUS_CANCELLED = 'iptal';

    protected $fillable = [
        'title',
        'amount',
        'due_date',
        'category',
        'status',
        'description',
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
        static::creating(function (self $plan): void {
            if (blank($plan->created_by) && Auth::check()) {
                $plan->created_by = Auth::id();
            }
        });
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

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->due_date->isPast();
    }

    /**
     * Planlanan gideri şimdi ödendi olarak işaretler ve ana kasaya
     * gider olarak işler.
     */
    public function markPaid(): void
    {
        if ($this->status === self::STATUS_PAID) {
            return;
        }

        $transaction = CashboxTransaction::query()->create([
            'type' => CashboxTransaction::TYPE_EXPENSE,
            'amount' => $this->amount,
            'category' => $this->category ?? 'Planlı Gider',
            'description' => $this->title,
            'date' => now()->toDateString(),
            'source' => 'planned_payment',
            'created_by' => $this->created_by,
        ]);

        $this->status = self::STATUS_PAID;
        $this->cashbox_transaction_id = $transaction->id;
        $this->save();
    }
}
