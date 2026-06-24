<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adm_mail_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mailer', 50)->default('smtp');
            $table->string('host')->nullable();
            $table->unsignedSmallInteger('port')->nullable();
            $table->string('encryption', 20)->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('from_address');
            $table->string('from_name');
            $table->boolean('is_active')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adm_mail_settings');
    }
};
