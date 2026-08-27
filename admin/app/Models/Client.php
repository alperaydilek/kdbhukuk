<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    protected $fillable = [
        'client_type',
        'first_name',
        'last_name',
        'company_name',
        'tc_no',
        'tax_no',
        'phone',
        'email',
        'address',
        'case_no',
        'status',
        'notes',
    ];

    /**
     * @return HasMany<ClientDebt, $this>
     */
    public function debts(): HasMany
    {
        return $this->hasMany(ClientDebt::class);
    }

    /**
     * @return HasMany<ClientPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(ClientPayment::class);
    }

    /**
     * @return HasMany<CashboxTransaction, $this>
     */
    public function cashboxTransactions(): HasMany
    {
        return $this->hasMany(CashboxTransaction::class);
    }

    /**
     * @return HasMany<CommunicationLog, $this>
     */
    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class);
    }

    public function fullName(): string
    {
        if ($this->client_type === 'kurumsal' && filled($this->company_name)) {
            return $this->company_name;
        }

        return trim("{$this->first_name} {$this->last_name}");
    }

    public function totalDebt(): float
    {
        return (float) $this->debts()->where('status', '!=', 'iptal')->sum('amount');
    }

    public function totalCollected(): float
    {
        return (float) $this->payments()->where('status', 'tahsil_edildi')->sum('amount');
    }

    public function totalPending(): float
    {
        return (float) $this->payments()->where('status', 'bekliyor')->sum('amount');
    }

    public function balance(): float
    {
        return $this->totalDebt() - $this->totalCollected();
    }
}
