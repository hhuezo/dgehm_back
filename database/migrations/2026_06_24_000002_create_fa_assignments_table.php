<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fa_assignments', function (Blueprint $table) {
            $table->id();

            $table->date('date');

            $table->foreignId('organizational_unit_id')
                ->constrained('fa_organizational_units');

            $table->foreignId('person_id')
                ->constrained('adm_employees');

            $table->text('observation')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fa_assignments');
    }
};
