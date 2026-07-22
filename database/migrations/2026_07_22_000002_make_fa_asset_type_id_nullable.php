<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Asegura asset_type_id nullable en instalaciones que lo tenían NOT NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fa_fixed_assets') || !Schema::hasColumn('fa_fixed_assets', 'asset_type_id')) {
            return;
        }

        DB::statement('ALTER TABLE fa_fixed_assets MODIFY asset_type_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        // no-op
    }
};
