<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->boolean('partial_delivery')->default(false)->after('file');
        });
    }

    public function down(): void
    {
        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->dropColumn('partial_delivery');
        });
    }
};
