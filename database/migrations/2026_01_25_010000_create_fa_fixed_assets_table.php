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
        Schema::create('fa_fixed_assets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fa_class_id')->constrained('fa_classes'); // clase (fa_classes_id)

            $table->string('code', 50); // código
            $table->string('correlative', 50); // correlativo

            $table->string('description', 255); // descripción
            $table->string('brand', 150); // marca
            $table->string('model', 150); // modelo
            $table->string('serial_number', 150)->nullable(); // serie
            $table->string('location', 255); // ubicación
            $table->string('policy', 150)->nullable(); // póliza
            $table->string('current_responsible', 255); // responsable_actual

            $table->foreignId('organizational_unit_id')->constrained('fa_organizational_units'); // unidad_id

            $table->string('asset_type', 150); // tipo_bien
            $table->date('acquisition_date'); // fecha_adquisicion

            $table->string('supplier', 255)->nullable(); // proveedor
            $table->string('invoice', 150)->nullable(); // factura

            $table->foreignId('origin_id')->constrained('fa_origins'); // origen (origin_id)

            $table->foreignId('physical_condition_id')->constrained('fa_physical_conditions'); // estado_fisico_id

            $table->text('additional_description')->nullable(); // descripción (adicional)
            $table->string('measurements', 255)->nullable(); // medidas
            $table->text('observation')->nullable(); // observación

            $table->boolean('is_insured')->default(false); // asegurado
            $table->string('insured_description', 255)->nullable(); // descripción_asegurado

            $table->decimal('purchase_value', 15, 4); // valor_compra

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fa_fixed_assets');
    }
};

