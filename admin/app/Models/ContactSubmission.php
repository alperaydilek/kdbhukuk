<?php

namespace App\Models;

use Database\Factories\ContactSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    /** @use HasFactory<ContactSubmissionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'subject',
        'message',
        'kvkk_consent',
        'status',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'kvkk_consent' => 'boolean',
        ];
    }
}
