<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wh_request_status', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->boolean('active')->default(true); // Campo active con valor por defecto true
            $table->timestamps();
        });

        // Opcional: Insertar los estados iniciales de ejemplo
        DB::table('wh_request_status')->insert([
            ['name' => 'Pendiente', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aprobada', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Completada', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rechazada', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_status');
    }
};
