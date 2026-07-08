<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_fa_organizational_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('fa_organizational_unit_id')
                ->constrained('fa_organizational_units')
                ->cascadeOnDelete();
            $table->unique(['user_id', 'fa_organizational_unit_id'], 'user_fa_ou_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_fa_organizational_unit');
    }
};
