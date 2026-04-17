<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use OwenIt\Auditing\Contracts\Auditable;

class Package extends Model implements Auditable
{
    use HasUuid, \OwenIt\Auditing\Auditable;
    
    protected $guarded = [];
}
