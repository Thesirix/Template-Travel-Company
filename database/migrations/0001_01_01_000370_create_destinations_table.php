<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 100);
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 7)->default('#4f46e5');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['slug'], 'destinations_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
