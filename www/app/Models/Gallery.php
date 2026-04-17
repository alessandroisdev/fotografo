<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Gallery extends Model
{
    use HasUuid;
    
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
