<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wh_products', function (Blueprint $table) {
            $table->string('sku', 50)->nullable()->unique()->after('id');
        });

        $rows = DB::table('wh_products')
            ->join('wh_accounting_accounts', 'wh_products.accounting_account_id', '=', 'wh_accounting_accounts.id')
            ->orderBy('wh_products.accounting_account_id')
            ->orderBy('wh_products.id')
            ->select(
                'wh_products.id',
                'wh_accounting_accounts.code as account_code'
            )
            ->get();

        $correlativesByCode = [];

        foreach ($rows as $row) {
            $code = (string) $row->account_code;
            $correlativesByCode[$code] = ($correlativesByCode[$code] ?? 0) + 1;
            $sku = $code . '-' . str_pad((string) $correlativesByCode[$code], 3, '0', STR_PAD_LEFT);

            DB::table('wh_products')->where('id', $row->id)->update(['sku' => $sku]);
        }
    }

    public function down(): void
    {
        Schema::table('wh_products', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn('sku');
        });
    }
};
