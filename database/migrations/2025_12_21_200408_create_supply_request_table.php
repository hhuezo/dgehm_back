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
        Schema::create('wh_supply_request', function (Blueprint $table) {
            $table->id();

            $table->timestamp('date');
            $table->text('observation')->nullable();

            $table->foreignId('requester_id')->constrained('users');

             $table->foreignId('office_id')->constrained('wh_offices');

            $table->foreignId('immediate_boss_id')
                  ->nullable()
                  ->constrained('users');

            $table->foreignId('delivered_by_id')
                  ->nullable()
                  ->constrained('users');

            $table->foreignId('status_id')
                  ->default(1)
                  ->constrained('wh_request_status');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wh_supply_request');
    }
};
