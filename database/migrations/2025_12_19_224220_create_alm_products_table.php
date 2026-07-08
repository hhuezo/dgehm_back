<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wh_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->nullable()->unique();
            $table->foreignId('accounting_account_id')->constrained('wh_accounting_accounts');
            $table->foreignId('measure_id')->constrained('wh_measures');
            $table->string('name', 150);
            $table->string('description', 255)->nullable();
            $table->integer('minimo')->nullable();
            $table->integer('maximo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('environmental_report')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wh_products');
    }
};
