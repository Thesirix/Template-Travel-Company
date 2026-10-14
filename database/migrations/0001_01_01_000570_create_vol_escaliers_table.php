<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vol_escaliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vol_id');
            $table->unsignedBigInteger('air_port_id');
            $table->unsignedBigInteger('re_air_port_id');
            $table->string('heure_arriver');
            $table->string('date_depart')->nullable();
            $table->string('heure_depart')->nullable();
            $table->string('temp')->nullable();
            $table->boolean('type')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('num', 10)->nullable();
            $table->string('model', 300)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vol_escaliers');
    }
};
