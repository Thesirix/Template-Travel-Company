<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vol_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vol_id');
            $table->unsignedBigInteger('vol_retour_id')->nullable();
            $table->string('type_reservation', 100)->default('Reservation');
            $table->string('type_voyage', 100)->default('aller_retour_same_vol');
            $table->string('type_payemant', 300)->nullable();
            $table->double('prix_total')->nullable();
            $table->double('prix_payed')->nullable();
            $table->string('a_compte', 50)->nullable();
            $table->integer('nb_adult')->default(1);
            $table->integer('nb_enfant')->default(0);
            $table->integer('nb_bebe')->default(0);
            $table->unsignedBigInteger('gent_id');
            $table->string('etat', 100)->default('en cour');
            $table->string('note', 500)->nullable();
            $table->text('code')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vol_reservations');
    }
};
