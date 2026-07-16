<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('fa_vehicle_brands');
        Schema::dropIfExists('fa_vehicle_colors');
        Schema::dropIfExists('fa_vehicle_drive_types');
        Schema::dropIfExists('fa_vehicle_types');

        $permissionNames = [
            'vehicle_brands view',
            'vehicle_brands create',
            'vehicle_brands update',
            'vehicle_brands delete',
            'vehicle_colors view',
            'vehicle_colors create',
            'vehicle_colors update',
            'vehicle_colors delete',
            'vehicle_drive_types view',
            'vehicle_drive_types create',
            'vehicle_drive_types update',
            'vehicle_drive_types delete',
            'vehicle_types view',
            'vehicle_types create',
            'vehicle_types update',
            'vehicle_types delete',
        ];

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $permissionNames)
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')
                ->whereIn('permission_id', $permissionIds)
                ->delete();

            if (Schema::hasTable('model_has_permissions')) {
                DB::table('model_has_permissions')
                    ->whereIn('permission_id', $permissionIds)
                    ->delete();
            }

            DB::table('permissions')
                ->whereIn('id', $permissionIds)
                ->delete();
        }
    }

    public function down(): void
    {
        Schema::create('fa_vehicle_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('fa_vehicle_colors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('fa_vehicle_drive_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('fa_vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }
};
