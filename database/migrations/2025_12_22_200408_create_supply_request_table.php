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
        Schema::create('supply_request', function (Blueprint $table) {
            $table->id();

            // Campos de la solicitud
            $table->timestamp('request_date'); // date
            $table->text('observation')->nullable(); // observation


            $table->foreignId('requester_id')->constrained('users'); // solicitante
            $table->text('immediate_boss_id')->nullable(); // jefe_inmediato
            $table->text('delivered_by_id')->nullable(); // entregado_por

            $table->foreignId('status_id')
                  ->constrained('request_status')
                  ->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supply_request');
    }
};
