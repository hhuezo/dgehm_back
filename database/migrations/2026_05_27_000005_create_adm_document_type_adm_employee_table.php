<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adm_document_type_adm_employee', function (Blueprint $table) {
            $table->id();
            $table->string('value')->unique();
            $table->foreignId('adm_employee_id')->constrained();
            $table->foreignId('adm_document_type_id')->constrained();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adm_document_type_adm_employee');
    }
};
