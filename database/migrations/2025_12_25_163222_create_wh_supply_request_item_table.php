<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wh_supply_request_detail', function (Blueprint $table) {
            $table->id();

            // Claves Foráneas
            $table->foreignId('supply_request_id')
                  ->constrained('wh_supply_request');

            $table->foreignId('product_id')
                  ->constrained('wh_products');

            // Campos de Datos
            $table->decimal('quantity', 10, 2);
            $table->decimal('delivered_quantity', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wh_supply_request_detail');
    }
};
