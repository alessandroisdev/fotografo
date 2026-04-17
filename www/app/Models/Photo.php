<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function gallery()
    {
        return $this->belongsTo(Gallery::class);
    }
}
