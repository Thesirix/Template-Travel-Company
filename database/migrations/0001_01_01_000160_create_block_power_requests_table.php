<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('block_power_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('agent_id');
            $table->string('name', 100)->nullable();
            $table->string('status', 20)->default('display');
            $table->string('company', 50)->nullable();
            $table->string('pnr')->nullable();
            $table->string('country', 100);
            $table->string('departure_city', 100);
            $table->string('arrival_city', 100);
            $table->date('date_aller');
            $table->date('date_retour')->nullable();
            $table->integer('seats');
            $table->string('cabin_class', 50);
            $table->double('price');
            $table->date('option_date');
            $table->date('pnr_created_on')->nullable();
            $table->string('target_class', 50);
            $table->string('target_price', 50);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('block_power_requests');
    }
};
