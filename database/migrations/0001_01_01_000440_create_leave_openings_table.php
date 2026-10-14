<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_openings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gent_id');
            $table->date('month_start');
            $table->decimal('opening', 8, 2)->default(0);
            $table->decimal('adjust', 8, 2)->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['month_start'], 'leave_openings_month_start_index');
            $table->unique(['gent_id', 'month_start'], 'u_gent_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_openings');
    }
};
