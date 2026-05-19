<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adm_employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('lastname');
            $table->string('email')->unique();
            $table->string('email_personal')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('phone_personal')->nullable();
            $table->string('photo_name')->nullable();
            $table->string('photo_route')->nullable();
            $table->string('photo_route_sm')->nullable();
            $table->date('birthday')->nullable();
            $table->boolean('marking_required')->default(true);
            $table->smallInteger('status')->default(1);
            $table->boolean('active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('adm_gender_id')->nullable()->constrained();
            $table->foreignId('adm_marital_status_id')->nullable()->constrained();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->boolean('remote_mark')->default(false);
            $table->boolean('external')->default(false);
            $table->boolean('viatic')->default(false);
            $table->boolean('children')->default(false);
            $table->text('unsubscribe_justification')->nullable();
            $table->boolean('vehicle')->default(false);
            $table->boolean('adhonorem')->default(false);
            $table->boolean('parking')->default(false);
            $table->boolean('disabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adm_employees');
    }
};
