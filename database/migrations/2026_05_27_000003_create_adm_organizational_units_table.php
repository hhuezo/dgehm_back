<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adm_organizational_units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('abbreviation')->nullable()->unique();
            $table->string('code', 32)->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('adm_organizational_unit_type_id')->constrained();
            $table->foreignId('adm_organizational_unit_id')->nullable()->constrained('adm_organizational_units');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adm_organizational_units');
    }
};
