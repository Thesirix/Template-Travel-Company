<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boat_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('trajet', 200);
            $table->unsignedBigInteger('insurance_id')->nullable();
            $table->unsignedBigInteger('destination_id');
            $table->unsignedBigInteger('departure_port_id');
            $table->unsignedBigInteger('arrival_port_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('accommodation_id')->nullable();
            $table->unsignedBigInteger('fare_id');
            $table->date('date_aller');
            $table->date('date_retour')->nullable();
            $table->integer('nb_adulte')->default(0);
            $table->integer('nb_senior')->default(0);
            $table->integer('nb_jeune')->default(0);
            $table->integer('nb_enfant')->default(0);
            $table->integer('nb_bebe')->default(0);
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('voiture_marque', 50)->nullable();
            $table->string('voiture_modele', 50)->nullable();
            $table->string('voiture_matricule', 50)->nullable();
            $table->unsignedBigInteger('vehicle_size_id')->nullable();
            $table->date('voiture_anne')->nullable();
            $table->unsignedBigInteger('vehicle_insurance_id')->nullable();
            $table->string('carte_grise', 200)->nullable();
            $table->string('note', 300)->nullable();
            $table->unsignedBigInteger('gent_id');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->index(['gent_id'], 'bateu_demandes_ibfk_1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boat_bookings');
    }
};
