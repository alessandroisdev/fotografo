<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status' => \App\Enums\DownloadStatusEnum::class,
    ];
}
