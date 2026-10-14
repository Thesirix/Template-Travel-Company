<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emplois', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gent_id');
            $table->string('title');
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('color')->default('#1067c4');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emplois');
    }
};
