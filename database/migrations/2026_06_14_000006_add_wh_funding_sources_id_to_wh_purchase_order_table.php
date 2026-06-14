<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->foreignId('wh_funding_sources_id')
                ->nullable()
                ->after('supplier_id')
                ->constrained('wh_funding_sources');
        });

        DB::table('wh_purchase_order')->update([
            'wh_funding_sources_id' => 1,
        ]);
    }

    public function down(): void
    {
        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->dropForeign(['wh_funding_sources_id']);
            $table->dropColumn('wh_funding_sources_id');
        });
    }
};
