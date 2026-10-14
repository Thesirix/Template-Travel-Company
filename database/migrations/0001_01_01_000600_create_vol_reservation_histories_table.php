<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vol_reservation_histories', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('reservation_id');
            $table->timestamp('created_at');
            $table->text('details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vol_reservation_histories');
    }
};
