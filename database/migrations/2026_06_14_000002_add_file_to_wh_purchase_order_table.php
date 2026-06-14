<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->string('file', 255)->nullable()->after('administrative_technician_id');
        });
    }

    public function down(): void
    {
        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->dropColumn('file');
        });
    }
};
