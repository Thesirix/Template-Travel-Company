<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('to_do_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gent_id');
            $table->string('text', 100);
            $table->dateTime('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('to_do_lists');
    }
};
