<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wh_supply_request', function (Blueprint $table) {
            $table->string('requester_file', 255)->nullable()->after('observation');
            $table->string('approver_file', 255)->nullable()->after('requester_file');
            $table->string('warehouse_manager_file', 255)->nullable()->after('approver_file');
        });
    }

    public function down(): void
    {
        Schema::table('wh_supply_request', function (Blueprint $table) {
            $table->dropColumn(['requester_file', 'approver_file', 'warehouse_manager_file']);
        });
    }
};
