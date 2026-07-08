<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wh_purchase_order', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supplier_id')->constrained('wh_suppliers');

            $table->foreignId('wh_funding_sources_id')
                ->nullable()
                ->constrained('wh_funding_sources');

            $table->string('order_number', 50);
            $table->string('budget_commitment_number', 50)->nullable();
            $table->dateTime('acta_date');
            $table->dateTime('reception_date');
            $table->string('supplier_representative', 150);
            $table->string('invoice_number', 50)->unique();
            $table->date('invoice_date');

            $table->foreignId('purchase_order_administrator_id')
                ->constrained('adm_employees');

            $table->foreignId('administrative_technician_id')
                ->nullable()
                ->constrained('adm_employees');

            $table->string('file', 255)->nullable();
            $table->boolean('partial_delivery')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wh_purchase_order');
    }
};
