<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wh_supply_returns', function (Blueprint $table) {
            $table->id();
            $table->date('return_date');
            $table->date('received_date')->nullable();

            $table->foreignId('returned_by_id')->constrained('users');
            $table->foreignId('fa_organizational_unit_id')
                ->constrained('fa_organizational_units')
                ->restrictOnDelete();
            $table->foreignId('immediate_supervisor_id')->constrained('users');
            $table->foreignId('received_by_id')->nullable()->constrained('users');

            $table->string('phone_extension', 10)->nullable();
            $table->text('general_observations')->nullable();

            $table->foreignId('approved_by_id')->nullable()->constrained('users');
            $table->foreignId('rejected_by_id')->nullable()->constrained('users');

            $table->foreignId('status_id')
                ->default(1)
                ->constrained('wh_request_status');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wh_supply_returns');
    }
};
