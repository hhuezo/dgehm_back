<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fa_assignments', function (Blueprint $table) {
            $table->text('observation')->nullable()->after('collaborator_id');
            $table->dropColumn('is_finalized');
        });
    }

    public function down(): void
    {
        Schema::table('fa_assignments', function (Blueprint $table) {
            $table->boolean('is_finalized')->default(false)->after('collaborator_id');
            $table->dropColumn('observation');
        });
    }
};
