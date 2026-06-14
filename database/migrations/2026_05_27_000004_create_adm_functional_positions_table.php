<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adm_functional_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('abbreviation')->nullable()->unique();
            $table->text('description')->nullable();
            $table->integer('amount_required')->default(1);
            $table->decimal('salary_min', 8, 2)->default(0.00);
            $table->decimal('salary_max', 8, 2)->default(0.00);
            $table->boolean('boss')->default(false);
            $table->smallInteger('boss_hierarchy')->default(0);
            $table->smallInteger('original')->nullable();
            $table->smallInteger('user_required')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('adm_organizational_unit_id')->nullable()->constrained();
            $table->foreignId('adm_functional_position_id')->nullable()->constrained('adm_functional_positions');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            $table->unique(['name', 'adm_organizational_unit_id', 'deleted_at'], 'unique_name_unit_id_delete_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adm_functional_positions');
    }
};
