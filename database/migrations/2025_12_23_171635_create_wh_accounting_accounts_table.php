<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wh_accounting_accounts', function (Blueprint $table) {
            $table->string('name')->unique();
            $table->integer('code')->unique();
        });
    }

    public function down(): void
    {
        Schema::table('wh_accounting_accounts', function (Blueprint $table) {
            $table->dropColumn(['name', 'code']);
        });
    }
};
