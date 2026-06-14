<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->foreignId('purchase_order_administrator_id')
                ->nullable()
                ->after('total_amount')
                ->constrained('adm_employees');
        });

        $defaultEmployeeId = DB::table('adm_employees')->orderBy('id')->value('id');

        if ($defaultEmployeeId) {
            DB::table('wh_purchase_order')->update([
                'purchase_order_administrator_id' => $defaultEmployeeId,
            ]);
        }

        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->dropColumn('administrative_manager');
        });
    }

    public function down(): void
    {
        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->string('administrative_manager', 150)->nullable()->after('total_amount');
        });

        $orders = DB::table('wh_purchase_order')
            ->leftJoin('adm_employees', 'wh_purchase_order.purchase_order_administrator_id', '=', 'adm_employees.id')
            ->select(
                'wh_purchase_order.id',
                'adm_employees.name',
                'adm_employees.lastname'
            )
            ->get();

        foreach ($orders as $order) {
            $name = trim(($order->name ?? '') . ' ' . ($order->lastname ?? ''));

            DB::table('wh_purchase_order')
                ->where('id', $order->id)
                ->update(['administrative_manager' => $name !== '' ? $name : null]);
        }

        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_administrator_id']);
            $table->dropColumn('purchase_order_administrator_id');
        });
    }
};
