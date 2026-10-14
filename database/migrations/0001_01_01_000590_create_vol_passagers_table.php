<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vol_passagers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('assurance_id');
            $table->string('type', 50);
            $table->string('genre', 50);
            $table->string('nom', 200);
            $table->string('prenom', 200);
            $table->date('date_naissance');
            $table->boolean('passport_en_cour')->default(false);
            $table->string('passport', 15)->nullable();
            $table->string('passport_image', 300)->nullable();
            $table->date('expiration')->nullable();
            $table->boolean('sijour_en_cour')->default(false);
            $table->string('sijour', 30)->nullable();
            $table->string('sijour_image', 300)->nullable();
            $table->date('sijour_expiration')->nullable();
            $table->string('tele1', 20)->nullable();
            $table->string('tele2', 20)->nullable();
            $table->string('tele_destination', 20)->nullable();
            $table->string('email', 300)->nullable();
            $table->string('assistance', 200)->nullable();
            $table->string('Observation', 300)->nullable();
            $table->string('dossier_etat', 100)->default('Incomplet');
            $table->string('verifier', 30)->default('NON');
            $table->boolean('inscrit_aller')->default(false);
            $table->boolean('inscrit_retour')->default(false);
            $table->string('ref_aller', 250)->nullable();
            $table->string('ref_retour', 250)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vol_passagers');
    }
};
