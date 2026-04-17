<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

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
}
