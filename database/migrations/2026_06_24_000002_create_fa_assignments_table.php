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

            $table->boolean('is_assignment')->default(false);
            $table->boolean('is_unassignment')->default(false);
            $table->date('date');

            $table->boolean('is_permanent')->default(false);
            $table->date('temporal_start_date')->nullable();
            $table->date('temporal_end_date')->nullable();

            $table->foreignId('organizational_unit_id')
                ->constrained('fa_organizational_units');

            $table->unsignedBigInteger('person_id')->nullable();
            $table->unsignedBigInteger('collaborator_id')->nullable();

            $table->text('observation')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fa_assignments');
    }
};
