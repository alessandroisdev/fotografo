<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('gallery_id')->constrained('galleries')->onDelete('cascade');
            
            $table->string('original_name');
            $table->string('original_path'); // Criptografado ou protegido na Storage
            $table->string('watermark_path')->nullable(); // Para o frontend público
            $table->string('thumbnail_path')->nullable(); // Para o painel admin
            
            $table->string('storage_driver')->default('local'); // local, s3, gdrive (Strategy Pattern target)
            $table->decimal('price', 10, 2)->nullable(); // Se a foto pode ter preo individual que override a default
            
            $table->string('status')->default('processing'); // processing, ready, cloud_archived
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('photos');
    }
};
