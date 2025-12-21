<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_wh_office', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('wh_office_id')
                ->constrained('wh_offices')
                ->cascadeOnDelete();

            // Opcional: si un usuario solo puede estar una vez por oficina
            $table->unique(['user_id', 'wh_office_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_wh_office');
    }
};
