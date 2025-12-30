<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wh_kardex', function (Blueprint $table) {

            $table->id();

            // Clave foránea a la cabecera (wh_purchase_order)
            $table->foreignId('purchase_order_id')
                  ->constrained('wh_purchase_order');

            $table->foreignId('supply_request_id')
                  ->constrained('wh_supply_request')->nullable();

            // Clave foránea al producto maestro (wh_products)
            $table->foreignId('product_id')
                  ->constrained('wh_products');

            $table->boolean('movement_type');

            // Campos de detalle
            $table->unsignedSmallInteger('quantity');
            $table->decimal('unit_price', 10, 4);
            $table->decimal('subtotal', 10, 4)->nullable();



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wh_kardex');
    }
};
