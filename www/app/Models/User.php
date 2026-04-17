<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;
use App\Traits\HasUuid;

#[Fillable(['name', 'email', 'password', 'role', 'uuid', 'document', 'phone', 'zipcode', 'address', 'address_number', 'city', 'state'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements Auditable
{
    use HasFactory, Notifiable, HasUuid, \OwenIt\Auditing\Auditable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => \App\Enums\UserRoleEnum::class,
        ];
    }

    public function cards()
    {
        return $this->hasMany(UserCard::class);
    }
}
