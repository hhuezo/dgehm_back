<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo fa_asset_types: estructura + datos iniciales (única fuente de seed).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fa_asset_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->timestamps();
        });

        $now = now();
        $names = [];

        for ($i = 1; $i <= 12; $i++) {
            $names[] = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
        }

        $names[] = '99';
        $names[] = 'EUROCLIMA';

        DB::table('fa_asset_types')->insert(
            array_map(
                static fn (string $name) => [
                    'name' => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                $names
            )
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('fa_asset_types');
    }
};
