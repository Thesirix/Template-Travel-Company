<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boat_passengers', function (Blueprint $table) {
            $table->id();
            $table->boolean('respo')->default(false);
            $table->string('nom', 200);
            $table->string('prenom', 200);
            $table->string('type', 100);
            $table->string('passport')->nullable();
            $table->string('passport_num', 15)->nullable();
            $table->date('date_naissance');
            $table->string('mail', 200)->nullable();
            $table->string('tele', 15)->nullable();
            $table->unsignedBigInteger('booking_id');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->integer('ageA')->nullable();
            $table->integer('ageR')->nullable();
            $table->index(['booking_id'], 'bateu_passagers_ibfk_1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boat_passengers');
    }
};
