<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('prenom')->nullable();
            $table->string('phone')->nullable();
            $table->string('adress')->nullable();
            $table->string('email');
            $table->text('info')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('agence_id');
            $table->date('contract_start_date')->nullable();
            $table->string('state', 100)->nullable();
            $table->boolean('is_super_agent')->default(false);
            $table->unique(['email'], 'gents_email_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gents');
    }
};
