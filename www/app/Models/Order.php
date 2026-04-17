<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use OwenIt\Auditing\Contracts\Auditable;

class Order extends Model implements Auditable
{
    use HasUuid, \OwenIt\Auditing\Auditable;
    
    protected $guarded = [];

    protected $casts = [
        'status' => \App\Enums\OrderStatusEnum::class,
        'gateway_payload' => 'array',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function package() {
        return $this->belongsTo(Package::class);
    }

    public function gallery() {
        return $this->belongsTo(Gallery::class);
    }

    public function items() {
        return $this->hasMany(OrderItem::class);
    }
    
    public function attempts() {
        return $this->hasMany(OrderAttempt::class)->orderBy('id', 'desc');
    }
}
