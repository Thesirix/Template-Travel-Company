<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boat_arrival_ports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('destination_id');
            $table->string('name', 200);
            $table->date('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boat_arrival_ports');
    }
};
