<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gestions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('prenom')->nullable();
            $table->string('phone')->nullable();
            $table->string('adresse')->nullable();
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['email'], 'gestions_email_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gestions');
    }
};
