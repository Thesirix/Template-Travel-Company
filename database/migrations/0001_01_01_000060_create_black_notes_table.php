<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('black_notes', function (Blueprint $table) {
            $table->id();
            $table->string('text', 100);
            $table->unsignedBigInteger('gent_id');
            $table->dateTime('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('black_notes');
    }
};
