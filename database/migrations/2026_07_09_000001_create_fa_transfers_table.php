<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fa_transfers', function (Blueprint $table) {
            $table->id();

            $table->date('date');

            $table->foreignId('organizational_unit_id')
                ->constrained('fa_organizational_units');

            $table->foreignId('person_delivers_id')
                ->constrained('adm_employees');

            $table->foreignId('person_receives_id')
                ->constrained('adm_employees');

            $table->text('observation')->nullable();
            $table->string('file', 255)->nullable();

            $table->foreignId('status_id')
                ->default(1)
                ->constrained('fa_movement_statuses')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fa_transfers');
    }
};
