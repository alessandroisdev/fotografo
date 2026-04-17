<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_holder',
        'card_brand',
        'last_four',
        'encrypted_number',
        'encrypted_expiry',
        'encrypted_cvv'
    ];

    /**
     * Engine de Criptografia em Memória: Transforma AES-256 strings blindadas em valores legíveis apenas na requisição atual
     */
    protected function casts(): array
    {
        return [
            'encrypted_number' => 'encrypted',
            'encrypted_expiry' => 'encrypted',
            'encrypted_cvv' => 'encrypted',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
