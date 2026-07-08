<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fa_fixed_assets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fa_category_id')->constrained('fa_categories');

            $table->string('code', 50);
            $table->string('correlative', 50);

            $table->string('description', 255);
            $table->string('brand', 150);
            $table->string('model', 150);
            $table->string('serial_number', 150)->nullable();
            $table->string('location', 255);
            $table->string('policy', 150)->nullable();
            $table->string('current_responsible', 255);

            $table->foreignId('organizational_unit_id')->constrained('fa_organizational_units');

            $table->string('asset_type', 150);
            $table->date('acquisition_date');

            $table->string('supplier', 255)->nullable();
            $table->string('invoice', 150)->nullable();

            $table->foreignId('origin_id')->constrained('fa_origins');
            $table->foreignId('physical_condition_id')->constrained('fa_physical_conditions');

            $table->text('additional_description')->nullable();
            $table->string('measurements', 255)->nullable();
            $table->text('observation')->nullable();

            $table->boolean('is_insured')->default(false);
            $table->string('insured_description', 255)->nullable();

            $table->decimal('purchase_value', 15, 4);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fa_fixed_assets');
    }
};
