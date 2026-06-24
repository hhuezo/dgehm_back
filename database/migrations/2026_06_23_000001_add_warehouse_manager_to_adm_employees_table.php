<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adm_employees', function (Blueprint $table) {
            $table->boolean('warehouse_manager')->default(false)->after('disabled');
        });
    }

    public function down(): void
    {
        Schema::table('adm_employees', function (Blueprint $table) {
            $table->dropColumn('warehouse_manager');
        });
    }
};
