<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boat_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('etat', 100)->default('confirmer');
            $table->unsignedBigInteger('booking_id');
            $table->string('num_reservation', 100);
            $table->date('date_option');
            $table->double('prix');
            $table->string('details', 500)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['booking_id'], 'bateu_reservations_ibfk_1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boat_reservations');
    }
};
