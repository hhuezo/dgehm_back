<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wh_supply_returns', function (Blueprint $table) {
            $table->date('received_date')
                ->nullable()
                ->after('return_date');

            $table->foreignId('approved_by_id')
                ->nullable()
                ->constrained('users');

            $table->foreignId('rejected_by_id')
                ->nullable()
                ->after('approved_by_id')
                ->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('wh_supply_returns', function (Blueprint $table) {
            $table->dropForeign(['approved_by_id']);
            $table->dropForeign(['rejected_by_id']);

            $table->dropColumn([
                'delivery_date',
                'approved_by_id',
                'rejected_by_id',
            ]);
        });
    }
};
