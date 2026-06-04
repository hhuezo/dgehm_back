<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wh_products', function (Blueprint $table) {
            $table->integer('minimo')->nullable()->after('description');
            $table->integer('maximo')->nullable()->after('minimo');
        });
    }

    public function down(): void
    {
        Schema::table('wh_products', function (Blueprint $table) {
            $table->dropColumn(['minimo', 'maximo']);
        });
    }
};
