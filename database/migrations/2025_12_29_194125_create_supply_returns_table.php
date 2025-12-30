<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wh_supply_returns', function (Blueprint $table) {
            $table->id(); // Columna 'id'
            $table->date('return_date');

            // Llaves Foráneas (Foreign Keys)
            $table->foreignId('returned_by_id')->constrained('users'); // La persona que devuelve
            $table->foreignId('wh_offices')->constrained(); // Oficina de origen
            $table->foreignId('immediate_supervisor_id')->constrained('users'); // El jefe inmediato
            $table->foreignId('received_by_id')->constrained('users'); // Quien recibe en almacén

            $table->string('phone_extension', 10)->nullable();
            $table->text('general_observations')->nullable();

            $table->timestamps(); // Columnas 'created_at' y 'updated_at'
        });
    }

       public function down(): void
    {
        Schema::dropIfExists('wh_supply_returns');
    }
};
