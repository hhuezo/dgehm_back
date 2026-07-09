<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fa_transfer_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fa_transfer_id')
                ->constrained('fa_transfers')
                ->cascadeOnDelete();

            $table->foreignId('fa_fixed_asset_id')
                ->constrained('fa_fixed_assets')
                ->restrictOnDelete();

            $table->text('observation')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fa_transfer_details');
    }
};
