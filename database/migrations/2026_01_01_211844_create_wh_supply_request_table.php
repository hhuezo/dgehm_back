<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wh_supply_request', function (Blueprint $table) {
            $table->id();

            $table->timestamp('date');
            $table->date('delivery_date')->nullable();
            $table->text('observation')->nullable();
            $table->string('requester_file', 255)->nullable();
            $table->string('approver_file', 255)->nullable();
            $table->string('warehouse_manager_file', 255)->nullable();

            $table->foreignId('requester_id')->constrained('users');

            $table->foreignId('fa_organizational_unit_id')
                ->constrained('fa_organizational_units')
                ->restrictOnDelete();

            $table->foreignId('immediate_boss_id')
                ->nullable()
                ->constrained('users');

            $table->foreignId('delivered_by_id')
                ->nullable()
                ->constrained('users');

            $table->foreignId('approved_by_id')
                ->nullable()
                ->constrained('users');

            $table->foreignId('rejected_by_id')
                ->nullable()
                ->constrained('users');

            $table->foreignId('status_id')
                ->default(1)
                ->constrained('wh_request_status');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wh_supply_request');
    }
};
