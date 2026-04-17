<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('zipcode', 15)->nullable()->after('phone');
            $table->string('address')->nullable()->after('zipcode');
            $table->string('address_number')->nullable()->after('address');
            $table->string('city')->nullable()->after('address_number');
            $table->string('state', 5)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['zipcode', 'address', 'address_number', 'city', 'state']);
        });
    }
};
