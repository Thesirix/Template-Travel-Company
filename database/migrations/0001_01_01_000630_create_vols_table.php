<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vols', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vol_group_id')->nullable();
            $table->string('destination', 150)->nullable();
            $table->unsignedBigInteger('ville_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->string('type_voyage', 50)->default('aller_retour');
            $table->date('date_aller')->nullable();
            $table->date('date_retour')->nullable();
            $table->time('duration_aller')->nullable();
            $table->time('duration_retour')->nullable();
            $table->time('heur_depart_aller');
            $table->time('heur_arriver_aller');
            $table->time('heur_depart_retour')->nullable();
            $table->time('heur_arriver_retour')->nullable();
            $table->string('period_voyage', 50)->default('Eté');
            $table->tinyInteger('is_archive')->nullable();
            $table->decimal('prix_achat_adult', 10, 0)->default(0);
            $table->decimal('prix_achat_enfant', 10, 0)->default(0);
            $table->decimal('prix_achat_bebe', 10, 0)->default(0);
            $table->decimal('prix_vente_adult', 11, 2)->default(0);
            $table->decimal('prix_vente_enfant', 11, 2)->default(0);
            $table->decimal('prix_vente_bebe', 11, 2)->default(0);
            $table->integer('place_total');
            $table->integer('place_disponible');
            $table->integer('place_reserver')->default(0);
            $table->integer('place_attente')->default(0);
            $table->integer('bagage')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('pnr')->nullable();
            $table->integer('y')->nullable();
            $table->integer('m')->nullable();
            $table->integer('d')->nullable();
            $table->integer('soute_num')->nullable();
            $table->integer('bg_hand')->nullable();
            $table->integer('maxbebe')->default(5);
            $table->string('itineraire', 300)->nullable();
            $table->string('itineraire_color', 10)->nullable();
            $table->unsignedBigInteger('condition_id')->nullable();
            $table->double('j_inscription')->default(150);
            $table->double('j_45')->default(300);
            $table->double('j_44_j_1')->default(150);
            $table->double('j_0')->default(100);
            $table->string('prix_comprend', 500)->nullable();
            $table->string('prix_comprend_pas', 500)->nullable();
            $table->double('total_a_payer')->default(300);
            $table->text('formalite')->nullable();
            $table->integer('days_before_closure')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vols');
    }
};
