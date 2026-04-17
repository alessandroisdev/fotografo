<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use OwenIt\Auditing\Contracts\Auditable;

class Gallery extends Model implements Auditable
{
    use HasUuid, \OwenIt\Auditing\Auditable;
    
    protected $guarded = [];

    protected $casts = [
        'status' => \App\Enums\GalleryStatusEnum::class,
        'is_public' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
