<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fa_movement_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('fa_movement_statuses')->insert([
            ['id' => 1, 'name' => 'Pendiente de aprobación', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Aprobado', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Rechazado', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Finalizado', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'Anulado', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('fa_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->nullable()->after('file');
        });

        Schema::table('fa_transfers', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->nullable()->after('file');
        });

        // Valores previos: 1 Ingresado → 1 Pendiente; 2 Finalizado → 4 Finalizado
        DB::table('fa_assignments')->update([
            'status_id' => DB::raw('CASE WHEN status = 2 THEN 4 ELSE 1 END'),
        ]);

        DB::table('fa_transfers')->update([
            'status_id' => DB::raw('CASE WHEN status = 2 THEN 4 ELSE 1 END'),
        ]);

        Schema::table('fa_assignments', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('fa_transfers', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        DB::statement('ALTER TABLE fa_assignments MODIFY status_id BIGINT UNSIGNED NOT NULL DEFAULT 1');
        DB::statement('ALTER TABLE fa_transfers MODIFY status_id BIGINT UNSIGNED NOT NULL DEFAULT 1');

        Schema::table('fa_assignments', function (Blueprint $table) {
            $table->foreign('status_id')
                ->references('id')
                ->on('fa_movement_statuses')
                ->restrictOnDelete();
        });

        Schema::table('fa_transfers', function (Blueprint $table) {
            $table->foreign('status_id')
                ->references('id')
                ->on('fa_movement_statuses')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fa_assignments', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
        });

        Schema::table('fa_transfers', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
        });

        Schema::table('fa_assignments', function (Blueprint $table) {
            $table->unsignedTinyInteger('status')->default(1)->after('observation');
        });

        Schema::table('fa_transfers', function (Blueprint $table) {
            $table->unsignedTinyInteger('status')->default(1)->after('observation');
        });

        DB::table('fa_assignments')->update([
            'status' => DB::raw('CASE WHEN status_id = 4 THEN 2 ELSE 1 END'),
        ]);

        DB::table('fa_transfers')->update([
            'status' => DB::raw('CASE WHEN status_id = 4 THEN 2 ELSE 1 END'),
        ]);

        Schema::table('fa_assignments', function (Blueprint $table) {
            $table->dropColumn('status_id');
        });

        Schema::table('fa_transfers', function (Blueprint $table) {
            $table->dropColumn('status_id');
        });

        Schema::dropIfExists('fa_movement_statuses');
    }
};
