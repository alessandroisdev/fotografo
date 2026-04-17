<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Package extends Model
{
    use HasUuid;
    
    protected $guarded = [];
}
