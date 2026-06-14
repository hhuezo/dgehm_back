<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adm_organizational_unit_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('staff')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adm_organizational_unit_types');
    }
};
