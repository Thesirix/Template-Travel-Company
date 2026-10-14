<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vol_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('country_id');
            $table->string('type', 100)->nullable();
            $table->boolean('same_itineraire')->default(true);
            $table->timestamp('created_at');
            $table->boolean('is_active')->default(true);
            $table->index(['name'], 'idx_vol_groups_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vol_groups');
    }
};
