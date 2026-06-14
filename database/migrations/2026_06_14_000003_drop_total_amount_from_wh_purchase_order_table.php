<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->decimal('total_amount', 12, 2)->default(0)->after('invoice_date');
        });
    }
};
