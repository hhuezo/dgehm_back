<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Respaldo: crea la pivot user_fa_organizational_unit si el deploy no ejecutó
 * 2026_05_19_100000_replace_wh_offices_with_organizational_units.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_fa_organizational_unit')) {
            Schema::create('user_fa_organizational_unit', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('fa_organizational_unit_id')
                    ->constrained('fa_organizational_units')
                    ->cascadeOnDelete();
                $table->unique(['user_id', 'fa_organizational_unit_id'], 'user_fa_ou_unique');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_wh_office')) {
            return;
        }

        $defaultTypeId = DB::table('fa_organizational_unit_types')->value('id');

        foreach (DB::table('user_wh_office')->get() as $row) {
            $ouId = null;

            if (Schema::hasTable('wh_offices')) {
                $officeName = DB::table('wh_offices')->where('id', $row->wh_office_id)->value('name');
                if ($officeName) {
                    $ouId = DB::table('fa_organizational_units')->where('name', $officeName)->value('id');
                    if (!$ouId && $defaultTypeId) {
                        $ouId = DB::table('fa_organizational_units')->insertGetId([
                            'name' => $officeName,
                            'abbreviation' => null,
                            'code' => null,
                            'is_active' => true,
                            'fa_organizational_unit_type_id' => $defaultTypeId,
                            'fa_organizational_unit_id' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            if ($ouId) {
                DB::table('user_fa_organizational_unit')->insertOrIgnore([
                    'user_id' => $row->user_id,
                    'fa_organizational_unit_id' => $ouId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // No revertir: la migración principal gestiona el ciclo de vida completo.
    }
};
