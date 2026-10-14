<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_vol_estimates', function (Blueprint $table) {
            $table->id();
            $table->string('origine_delete', 50)->nullable();
            $table->longText('comment')->nullable();
            $table->unsignedBigInteger('agence_id')->nullable();
            $table->unsignedBigInteger('gent_id')->nullable();
            $table->dateTime('created_at');
            $table->string('destination');
            $table->string('formule');
            $table->string('transport');
            $table->string('company');
            $table->string('departure_city');
            $table->string('arrived_city');
            $table->date('departure_date');
            $table->date('return_date')->nullable();
            $table->integer('nb_passagers_adultes');
            $table->integer('nb_passagers_childs');
            $table->integer('nb_passagers_babies');
            $table->bigInteger('selled');
            $table->double('proposed_price');
            $table->unsignedBigInteger('client_id');
            $table->boolean('is_vente')->default(false);
            $table->string('installation')->nullable();
            $table->string('vehicule')->nullable();
            $table->string('bagage')->nullable();
            $table->decimal('marge', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_vol_estimates');
    }
};
