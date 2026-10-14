<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id');
            $table->string('nom');
            $table->boolean('type')->default(true);
            $table->boolean('show')->default(false);
            $table->string('image')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->boolean('chart')->default(false);
            $table->integer('contrat')->default(1);
            $table->boolean('site')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
