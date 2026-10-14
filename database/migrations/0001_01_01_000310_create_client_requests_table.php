<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agence_id');
            $table->integer('gent_id');
            $table->dateTime('created_at');
            $table->string('destination');
            $table->string('formule', 50);
            $table->string('departure_city');
            $table->string('arrived_city');
            $table->date('departure_date');
            $table->date('return_date')->nullable();
            $table->integer('nb_passagers_adultes')->default(0);
            $table->integer('nb_passagers_childs')->default(0);
            $table->integer('nb_passagers_babies')->default(0);
            $table->string('bagage')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_requests');
    }
};
