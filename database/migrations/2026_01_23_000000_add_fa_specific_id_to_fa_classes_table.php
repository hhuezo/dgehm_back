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
        DB::table('fa_classes')->truncate();

        Schema::table('fa_classes', function (Blueprint $table) {
            $table->foreignId('fa_specific_id')->after('id')->constrained('fa_specifics');
            $table->string('code', 20)->after('fa_specific_id');
            $table->unique(['fa_specific_id', 'name']);
            $table->unique(['fa_specific_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fa_classes', function (Blueprint $table) {
            $table->dropUnique(['fa_specific_id', 'code']);
            $table->dropUnique(['fa_specific_id', 'name']);
            $table->dropForeign(['fa_specific_id']);
            $table->dropColumn('code');
        });
    }
};
