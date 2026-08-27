<?php

namespace App\Models;

use Database\Factories\ServiceFaqFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceFaq extends Model
{
    /** @use HasFactory<ServiceFaqFactory> */
    use HasFactory;

    protected $fillable = [
        'service_id',
        'order',
        'question',
        'answer',
    ];

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
