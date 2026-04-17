<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAttempt extends Model
{
    protected $fillable = ['order_id', 'gateway', 'payment_method', 'status', 'message', 'response_payload'];

    protected $casts = [
        'response_payload' => 'array'
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }
}
