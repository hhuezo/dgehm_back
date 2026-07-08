<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wh_kardex', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')
                ->constrained('wh_purchase_order');

            $table->foreignId('supply_request_id')
                ->nullable()
                ->constrained('wh_supply_request');

            $table->foreignId('supply_return_id')
                ->nullable()
                ->constrained('wh_supply_returns');

            $table->foreignId('product_id')
                ->constrained('wh_products');

            $table->boolean('movement_type');

            $table->unsignedSmallInteger('quantity');
            $table->decimal('unit_price', 10, 4);
            $table->decimal('subtotal', 10, 4)->nullable();
            $table->string('lot_number', 100)->nullable();
            $table->date('expiration_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wh_kardex');
    }
};
