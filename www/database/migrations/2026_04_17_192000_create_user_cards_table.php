<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Dados Públicos Seguros
            $table->string('card_holder');
            $table->string('card_brand')->nullable();
            $table->string('last_four', 4);
            
            // Cofre Criptografado (AES-256 via Laravel Cast)
            $table->text('encrypted_number');
            $table->text('encrypted_expiry');
            $table->text('encrypted_cvv');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_cards');
    }
};
