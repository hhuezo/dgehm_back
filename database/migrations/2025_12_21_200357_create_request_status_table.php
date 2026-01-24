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
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('wh_request_status')->insert([
            ['name' => 'Pendiente', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Enviada', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aprobada', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Completada', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rechazada', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wh_request_status');
    }
};
