<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'empresa_id',
        'mp_subscription_id',
        'mp_plan_id',
        'status',
        'next_payment_date',
        'payer_email',
        'reason',
        'amount',
        'currency_id',
        'frequency',
        'frequency_type',
        'external_reference',
        'init_point',
        'raw_response',
    ];

    protected $casts = [
        'next_payment_date' => 'datetime',
        'amount' => 'decimal:2',
        'frequency' => 'integer',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}