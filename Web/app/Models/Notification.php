<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'transaction_time',
        'transaction_status',
        'transaction_id',
        'status_message',
        'status_code',
        'signature_key',
        'settlement_time',
        'payment_type',
        'order_id',
        'merchant_id',
        'gross_amount',
        'fraud_status',
        'currency',
    ];

    protected $casts = [
        'transaction_time' => 'datetime',
        'settlement_time' => 'datetime',
        'gross_amount' => 'decimal:2',
    ];
}
