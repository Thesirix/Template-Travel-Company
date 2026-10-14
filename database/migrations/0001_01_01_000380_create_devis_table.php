<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            $table->string('departure_city', 250);
            $table->string('arrival_city', 250);
            $table->date('departure_date');
            $table->date('return_date')->nullable();
            $table->integer('passengers_nb');
            $table->string('first_name', 250);
            $table->string('last_name', 250);
            $table->string('phone_num', 250);
            $table->string('email', 250);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devis');
    }
};
