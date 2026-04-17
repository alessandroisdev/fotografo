<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            $table->string('zip_path')->nullable();
            $table->string('password')->nullable(); // Senha restrita de acesso opcional
            
            $table->string('cloud_driver')->default('local'); // Quando transferir para S3 / GDrive
            
            $table->string('status')->default('pending'); // pending, processing, ready, archived_in_cloud
            $table->timestamp('expires_at')->nullable(); // Ex: 3 meses gerado localmente
            
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('downloads');
    }
};
