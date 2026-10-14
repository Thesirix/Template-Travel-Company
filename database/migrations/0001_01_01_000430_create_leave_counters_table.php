<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_counters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gent_id');
            $table->enum('type', ['conge', 'repos', 'ferie', 'maladie', 'autre']);
            $table->decimal('balance', 5, 2)->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['gent_id', 'type'], 'uniq_gent_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_counters');
    }
};
