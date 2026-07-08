<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fa_category_employee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fa_category_id')
                ->constrained('fa_categories')
                ->cascadeOnDelete();
            $table->foreignId('adm_employee_id')
                ->constrained('adm_employees')
                ->cascadeOnDelete();
            $table->unique(['fa_category_id', 'adm_employee_id'], 'fa_category_employee_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fa_category_employee');
    }
};
