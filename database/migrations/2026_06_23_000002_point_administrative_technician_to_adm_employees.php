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
            $table->dropForeign(['administrative_technician_id']);
        });

        $orders = DB::table('wh_purchase_order')
            ->whereNotNull('administrative_technician_id')
            ->get(['id', 'administrative_technician_id']);

        foreach ($orders as $order) {
            $currentId = (int) $order->administrative_technician_id;

            $employeeId = DB::table('adm_employees')->where('id', $currentId)->value('id')
                ?? DB::table('adm_employees')->where('user_id', $currentId)->value('id')
                ?? DB::table('adm_employees')->where('warehouse_manager', true)->orderBy('id')->value('id')
                ?? DB::table('adm_employees')->orderBy('id')->value('id');

            if ($employeeId) {
                DB::table('wh_purchase_order')
                    ->where('id', $order->id)
                    ->update(['administrative_technician_id' => $employeeId]);
            }
        }

        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->foreign('administrative_technician_id')
                ->references('id')
                ->on('adm_employees');
        });
    }

    public function down(): void
    {
        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->dropForeign(['administrative_technician_id']);
        });

        $orders = DB::table('wh_purchase_order')
            ->whereNotNull('administrative_technician_id')
            ->get(['id', 'administrative_technician_id']);

        foreach ($orders as $order) {
            $userId = DB::table('adm_employees')
                ->where('id', $order->administrative_technician_id)
                ->value('user_id');

            if ($userId) {
                DB::table('wh_purchase_order')
                    ->where('id', $order->id)
                    ->update(['administrative_technician_id' => $userId]);
            }
        }

        Schema::table('wh_purchase_order', function (Blueprint $table) {
            $table->foreign('administrative_technician_id')
                ->references('id')
                ->on('users');
        });
    }
};
