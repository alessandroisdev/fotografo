<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->nullable()->constrained()->onDelete('set null'); // Opcional, pode ser compra totalmente avulsa
            $table->foreignId('gallery_id')->constrained()->onDelete('cascade');
            
            $table->integer('total_photos')->default(0);
            $table->integer('included_photos')->default(0); // Qts do pacote
            $table->integer('extra_photos')->default(0); // Qts pagos por fora
            
            $table->decimal('total_amount', 10, 2)->default(0.00); // Valor total devido (extra)
            $table->string('gateway')->nullable(); // pix, manual, credit_card
            $table->string('gateway_transaction_id')->nullable();
            
            $table->string('status')->default('pending'); // pending, paid, cancelled
            $table->timestamp('paid_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('orders');
    }
};
