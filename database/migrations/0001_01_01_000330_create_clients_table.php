<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('firstName', 100)->nullable();
            $table->string('lastName', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phoneNumber', 20)->nullable();
            $table->string('provenance')->nullable();
            $table->string('password', 250)->nullable();
            $table->timestamp('created_at');
            $table->boolean('statut')->default(false);
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->text('observation')->nullable();
            $table->date('recall')->nullable();
            $table->date('expire_passport')->nullable();
            $table->unsignedBigInteger('gent_id')->nullable();
            $table->unique(['email'], 'email_2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
