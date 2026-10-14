<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gent_id');
            $table->string('name', 100);
            $table->enum('type', ['conge', 'repos', 'ferie', 'maladie', 'autre'])->default('conge');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->string('freq', 100)->nullable();
            $table->string('status', 20)->default('pending');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->json('jours_par_type')->nullable();
            $table->string('modification_token', 36)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendars');
    }
};
