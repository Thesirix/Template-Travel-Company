<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assurences', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100)->default('Toutes');
            $table->string('name', 500);
            $table->text('description');
            $table->double('prix');
            $table->dateTime('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assurences');
    }
};
