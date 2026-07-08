<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fa_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fa_specific_id')->constrained('fa_specifics');
            $table->string('code', 20);
            $table->string('name', 150);
            $table->integer('useful_life')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['fa_specific_id', 'name']);
            $table->unique(['fa_specific_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fa_categories');
    }
};
