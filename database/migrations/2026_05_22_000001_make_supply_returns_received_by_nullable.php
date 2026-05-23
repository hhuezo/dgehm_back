<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wh_supply_returns', function (Blueprint $table) {
            $table->dropForeign(['received_by_id']);
            $table->unsignedBigInteger('received_by_id')->nullable()->change();
            $table->foreign('received_by_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('wh_supply_returns', function (Blueprint $table) {
            $table->dropForeign(['received_by_id']);
            $table->unsignedBigInteger('received_by_id')->nullable(false)->change();
            $table->foreign('received_by_id')->references('id')->on('users');
        });
    }
};
