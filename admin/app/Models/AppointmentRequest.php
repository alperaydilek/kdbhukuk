<?php

namespace App\Models;

use Database\Factories\AppointmentRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentRequest extends Model
{
    /** @use HasFactory<AppointmentRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'status',
        'note',
    ];
}
