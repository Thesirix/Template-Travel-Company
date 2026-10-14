<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_historiques', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('fullname', 100);
            $table->string('adresse', 200);
            $table->string('ville', 100);
            $table->integer('zip');
            $table->string('telephone', 12);
            $table->string('email', 200);
            $table->double('price');
            $table->text('text');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_historiques');
    }
};
