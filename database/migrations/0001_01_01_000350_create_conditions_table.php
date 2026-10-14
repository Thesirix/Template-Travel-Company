<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conditions', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 200);
            $table->text('Formalite_sante');
            $table->text('Formalite_police');
            $table->text('Formalite_inscription');
            $table->text('Formalite_modifiation');
            $table->text('Formalite_annulation');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conditions');
    }
};
