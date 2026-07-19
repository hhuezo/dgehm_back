<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fa_depreciation_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('fa_depreciation_statuses')->insert([
            ['id' => 1, 'name' => 'Pendiente de asignación', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Activo / depreciado', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'En espera', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Dado de baja', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('fa_fixed_assets', function (Blueprint $table) {
            $table->foreignId('depreciation_status_id')
                ->nullable()
                ->after('purchase_value')
                ->constrained('fa_depreciation_statuses');
        });

        DB::table('fa_fixed_assets')->whereNull('depreciation_status_id')->update([
            'depreciation_status_id' => 2,
        ]);

        DB::statement('ALTER TABLE fa_fixed_assets MODIFY depreciation_status_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE fa_fixed_assets MODIFY current_responsible VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE fa_fixed_assets SET current_responsible = '' WHERE current_responsible IS NULL");
        DB::statement('ALTER TABLE fa_fixed_assets MODIFY current_responsible VARCHAR(255) NOT NULL');

        Schema::table('fa_fixed_assets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('depreciation_status_id');
        });

        Schema::dropIfExists('fa_depreciation_statuses');
    }
};
