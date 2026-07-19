<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fa_fixed_assets', function (Blueprint $table) {
            $table->string('brand', 150)->nullable()->change();
            $table->string('model', 150)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('fa_fixed_assets', function (Blueprint $table) {
            $table->string('brand', 150)->nullable(false)->change();
            $table->string('model', 150)->nullable(false)->change();
        });
    }
};
