<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('block_power_passengers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('option_id');
            $table->string('type', 20);
            $table->string('last_name', 100);
            $table->string('first_name', 100);
            $table->string('sale_price', 500)->nullable();
            $table->string('deposit', 500)->nullable();
            $table->string('deposit_number', 500)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['option_id'], 'optimisationreservations_ibfk_1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('block_power_passengers');
    }
};
