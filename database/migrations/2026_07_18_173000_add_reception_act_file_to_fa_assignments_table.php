<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fa_assignments', function (Blueprint $table) {
            $table->string('reception_act_file', 255)->nullable()->after('file');
        });
    }

    public function down(): void
    {
        Schema::table('fa_assignments', function (Blueprint $table) {
            $table->dropColumn('reception_act_file');
        });
    }
};
