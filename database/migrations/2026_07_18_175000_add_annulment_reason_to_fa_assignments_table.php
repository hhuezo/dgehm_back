<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fa_assignments', function (Blueprint $table) {
            $table->text('annulment_reason')->nullable()->after('reception_act_file');
        });
    }

    public function down(): void
    {
        Schema::table('fa_assignments', function (Blueprint $table) {
            $table->dropColumn('annulment_reason');
        });
    }
};
