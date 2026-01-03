<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wh_supply_returns_detail', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supply_return_id')
                  ->constrained('wh_supply_returns');

            $table->foreignId('product_id')->constrained('wh_products');

            $table->unsignedInteger('returned_quantity');
            $table->text('observation')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wh_supply_returns_detail');
    }
};
