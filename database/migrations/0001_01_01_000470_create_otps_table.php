<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('otp', 6);
            $table->string('type', 50)->nullable();
            $table->dateTime('expiration_date');
            $table->integer('nb_attempts')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['email', 'type'], 'otps_email_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
