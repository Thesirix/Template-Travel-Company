<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boat_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 500);
            $table->dateTime('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boat_vehicles');
    }
};
