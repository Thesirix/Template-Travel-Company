<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gent_id');
            $table->date('date');
            $table->timestamp('heure_arrivee')->useCurrent();
            $table->string('ip_adresse', 45)->nullable();
            $table->string('mac_adresse', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
