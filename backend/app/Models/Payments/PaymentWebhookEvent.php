<?php

namespace App\Models\Payments;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class PaymentWebhookEvent extends Model
{
    use BelongsToSchool;

    protected $table = 'payment_webhook_events';

    protected $fillable = [
        'school_id',
        'provider',
        'event_type',
        'event_id',
        'payload',
        'signature',
        'processed',
        'processed_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'processed' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }
}
