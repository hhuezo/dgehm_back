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
        // Orden de Compra (Acta de Recepción)
        Schema::create('wh_purchase_order', function (Blueprint $table) {
            $table->id();

            // Proveedor
            $table->foreignId('supplier_id')->constrained('wh_suppliers');

            // Número de orden de compra
            $table->string('order_number', 50)->unique();

            // Compromiso presupuestario
            $table->string('budget_commitment_number', 50)->nullable();

            // Fecha Acta
            $table->dateTime('acta_date');

            // Fecha y hora
            $table->dateTime('reception_date');

            // Representante proveedor
            $table->string('supplier_representative', 150);

            // Número de factura
            $table->string('invoice_number', 50)->unique();

            // Fecha factura
            $table->date('invoice_date');

            // Monto total
            $table->decimal('total_amount', 12, 2);

            // Gerente administrativo
            $table->string('administrative_manager', 150);

            // Tecnico administrativo
            $table->foreignId('administrative_technician_id')->nullable()->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wh_purchase_order');
    }
};
