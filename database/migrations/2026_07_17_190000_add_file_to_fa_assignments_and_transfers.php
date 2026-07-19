<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fa_assignments', function (Blueprint $table) {
            $table->string('file', 255)->nullable()->after('observation');
        });

        Schema::table('fa_transfers', function (Blueprint $table) {
            $table->string('file', 255)->nullable()->after('observation');
        });
    }

    public function down(): void
    {
        Schema::table('fa_assignments', function (Blueprint $table) {
            $table->dropColumn('file');
        });

        Schema::table('fa_transfers', function (Blueprint $table) {
            $table->dropColumn('file');
        });
    }
};
