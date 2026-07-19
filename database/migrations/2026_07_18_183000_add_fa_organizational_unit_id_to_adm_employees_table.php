<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adm_employees', function (Blueprint $table) {
            $table->foreignId('fa_organizational_unit_id')
                ->nullable()
                ->after('adm_marital_status_id')
                ->constrained('fa_organizational_units')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('adm_employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fa_organizational_unit_id');
        });
    }
};
