<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Asegura catálogo fa_asset_types y, si aplica, convierte asset_type (string) → asset_type_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $names = [
            '01', '02', '03', '04', '05', '06',
            '07', '08', '09', '10', '11', '12',
            '99', 'EUROCLIMA','Transferencia EUROCLIMA'
        ];

        if (Schema::hasTable('fa_asset_types')) {
            foreach ($names as $name) {
                $exists = DB::table('fa_asset_types')->where('name', $name)->exists();
                if ($exists) {
                    continue;
                }

                DB::table('fa_asset_types')->insert([
                    'name' => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (!Schema::hasTable('fa_fixed_assets')) {
            return;
        }

        if (!Schema::hasColumn('fa_fixed_assets', 'asset_type')) {
            return;
        }

        if (!Schema::hasColumn('fa_fixed_assets', 'asset_type_id')) {
            Schema::table('fa_fixed_assets', function (Blueprint $table) {
                $table->unsignedBigInteger('asset_type_id')
                    ->nullable()
                    ->after('organizational_unit_id');
            });
        }

        $types = DB::table('fa_asset_types')->pluck('id', 'name');

        foreach (DB::table('fa_fixed_assets')->select('id', 'asset_type')->get() as $asset) {
            $raw = trim((string) ($asset->asset_type ?? ''));

            if ($raw === '') {
                DB::table('fa_fixed_assets')
                    ->where('id', $asset->id)
                    ->update(['asset_type_id' => null]);
                continue;
            }

            $normalized = preg_match('/^\d+$/', $raw)
                ? str_pad($raw, 2, '0', STR_PAD_LEFT)
                : $raw;

            DB::table('fa_fixed_assets')
                ->where('id', $asset->id)
                ->update(['asset_type_id' => $types[$normalized] ?? null]);
        }

        Schema::table('fa_fixed_assets', function (Blueprint $table) {
            $table->dropColumn('asset_type');
        });

        try {
            Schema::table('fa_fixed_assets', function (Blueprint $table) {
                $table->foreign('asset_type_id')->references('id')->on('fa_asset_types');
            });
        } catch (\Throwable) {
            // La FK ya existía
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('fa_fixed_assets') || !Schema::hasColumn('fa_fixed_assets', 'asset_type_id')) {
            return;
        }

        if (Schema::hasColumn('fa_fixed_assets', 'asset_type')) {
            return;
        }

        Schema::table('fa_fixed_assets', function (Blueprint $table) {
            $table->string('asset_type', 150)->nullable()->after('organizational_unit_id');
        });

        $types = DB::table('fa_asset_types')->pluck('name', 'id');

        foreach (DB::table('fa_fixed_assets')->select('id', 'asset_type_id')->get() as $asset) {
            DB::table('fa_fixed_assets')
                ->where('id', $asset->id)
                ->update(['asset_type' => $types[$asset->asset_type_id] ?? null]);
        }

        Schema::table('fa_fixed_assets', function (Blueprint $table) {
            $table->dropForeign(['asset_type_id']);
            $table->dropColumn('asset_type_id');
        });
    }
};
