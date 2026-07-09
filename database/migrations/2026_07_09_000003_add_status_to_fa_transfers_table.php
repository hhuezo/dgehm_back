<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fa_transfers', function (Blueprint $table) {
            $table->unsignedTinyInteger('status')
                ->default(1)
                ->after('observation');
        });
    }

    public function down(): void
    {
        Schema::table('fa_transfers', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
