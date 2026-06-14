<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adm_employee_adm_functional_position', function (Blueprint $table) {
            $table->id();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->boolean('principal')->default(true);
            $table->decimal('salary', 9, 2)->default(0.00);
            $table->boolean('active')->default(true);
            $table->foreignId('adm_employee_id')
                ->constrained('adm_employees', 'id')
                ->onDelete('cascade')
                ->name('fk_emp_func_employee');
            $table->foreignId('adm_functional_position_id')
                ->constrained('adm_functional_positions', 'id')
                ->onDelete('cascade')
                ->name('fk_emp_func_position');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adm_employee_adm_functional_position');
    }
};
