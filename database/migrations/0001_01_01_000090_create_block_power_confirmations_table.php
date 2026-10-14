<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('block_power_confirmations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('passenger_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['agent_id'], 'optimisationreservationssuccess_ibfk_1');
            $table->index(['passenger_id'], 'optimisationreservationssuccess_ibfk_2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('block_power_confirmations');
    }
};
