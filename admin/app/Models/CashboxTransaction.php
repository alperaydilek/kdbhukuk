<?php

namespace App\Models;

use Database\Factories\CashboxTransactionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashboxTransaction extends Model
{
    /** @use HasFactory<CashboxTransactionFactory> */
    use HasFactory;

    public const TYPE_INCOME = 'gelir';

    public const TYPE_EXPENSE = 'gider';

    public const TYPE_DEPOSIT = 'kasa_giris';

    public const TYPE_WITHDRAWAL = 'kasa_cikis';

    protected $fillable = [
        'type',
        'amount',
        'category',
        'description',
        'date',
        'client_id',
        'source',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Increasing transaction types (add to the balance).
     *
     * @return array<int, string>
     */
    public static function increasingTypes(): array
    {
        return [self::TYPE_INCOME, self::TYPE_DEPOSIT];
    }

    /**
     * Decreasing transaction types (subtract from the balance).
     *
     * @return array<int, string>
     */
    public static function decreasingTypes(): array
    {
        return [self::TYPE_EXPENSE, self::TYPE_WITHDRAWAL];
    }

    public static function currentBalance(): float
    {
        $increasing = (float) static::query()->whereIn('type', self::increasingTypes())->sum('amount');
        $decreasing = (float) static::query()->whereIn('type', self::decreasingTypes())->sum('amount');

        return $increasing - $decreasing;
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('date', '<=', $to));
    }
}
