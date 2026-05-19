<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_fa_organizational_unit')) {
            Schema::create('user_fa_organizational_unit', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('fa_organizational_unit_id')
                    ->constrained('fa_organizational_units')
                    ->cascadeOnDelete();
                $table->unique(['user_id', 'fa_organizational_unit_id'], 'user_fa_ou_unique');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('wh_supply_request') && !Schema::hasColumn('wh_supply_request', 'fa_organizational_unit_id')) {
            Schema::table('wh_supply_request', function (Blueprint $table) {
                if (Schema::hasColumn('wh_supply_request', 'office_id')) {
                    $table->unsignedBigInteger('fa_organizational_unit_id')->nullable()->after('office_id');
                } else {
                    $table->unsignedBigInteger('fa_organizational_unit_id')->nullable();
                }
            });
        }

        if (Schema::hasTable('wh_supply_returns') && !Schema::hasColumn('wh_supply_returns', 'fa_organizational_unit_id')) {
            Schema::table('wh_supply_returns', function (Blueprint $table) {
                if (Schema::hasColumn('wh_supply_returns', 'wh_office_id')) {
                    $table->unsignedBigInteger('fa_organizational_unit_id')->nullable()->after('wh_office_id');
                } else {
                    $table->unsignedBigInteger('fa_organizational_unit_id')->nullable();
                }
            });
        }

        $defaultTypeId = DB::table('fa_organizational_unit_types')->value('id');
        $officeToOu = [];

        if (Schema::hasTable('wh_offices')) {
            foreach (DB::table('wh_offices')->get() as $office) {
                $ouId = DB::table('fa_organizational_units')->where('name', $office->name)->value('id');

                if (!$ouId && $defaultTypeId) {
                    $ouId = DB::table('fa_organizational_units')->insertGetId([
                        'name' => $office->name,
                        'abbreviation' => null,
                        'code' => null,
                        'is_active' => (bool) ($office->is_active ?? true),
                        'fa_organizational_unit_type_id' => $defaultTypeId,
                        'fa_organizational_unit_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if ($ouId) {
                    $officeToOu[$office->id] = $ouId;
                }
            }

            foreach ($officeToOu as $officeId => $ouId) {
                if (Schema::hasColumn('wh_supply_request', 'office_id')) {
                    DB::table('wh_supply_request')
                        ->where('office_id', $officeId)
                        ->update(['fa_organizational_unit_id' => $ouId]);
                }

                if (Schema::hasColumn('wh_supply_returns', 'wh_office_id')) {
                    DB::table('wh_supply_returns')
                        ->where('wh_office_id', $officeId)
                        ->update(['fa_organizational_unit_id' => $ouId]);
                }

                if (Schema::hasTable('user_wh_office')) {
                    $userIds = DB::table('user_wh_office')
                        ->where('wh_office_id', $officeId)
                        ->pluck('user_id');

                    foreach ($userIds as $userId) {
                        DB::table('user_fa_organizational_unit')->insertOrIgnore([
                            'user_id' => $userId,
                            'fa_organizational_unit_id' => $ouId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        if (Schema::hasTable('wh_supply_request') && Schema::hasColumn('wh_supply_request', 'office_id')) {
            Schema::table('wh_supply_request', function (Blueprint $table) {
                $table->dropForeign(['office_id']);
                $table->dropColumn('office_id');
            });
        }

        if (
            Schema::hasTable('wh_supply_request')
            && Schema::hasColumn('wh_supply_request', 'fa_organizational_unit_id')
        ) {
            $fkName = $this->foreignKeyName('wh_supply_request', 'fa_organizational_unit_id');
            if (!$this->foreignKeyExists('wh_supply_request', $fkName)) {
                Schema::table('wh_supply_request', function (Blueprint $table) {
                    $table->foreign('fa_organizational_unit_id')
                        ->references('id')
                        ->on('fa_organizational_units')
                        ->restrictOnDelete();
                });
            }
        }

        if (Schema::hasTable('wh_supply_returns') && Schema::hasColumn('wh_supply_returns', 'wh_office_id')) {
            Schema::table('wh_supply_returns', function (Blueprint $table) {
                $table->dropForeign(['wh_office_id']);
                $table->dropColumn('wh_office_id');
            });
        }

        if (
            Schema::hasTable('wh_supply_returns')
            && Schema::hasColumn('wh_supply_returns', 'fa_organizational_unit_id')
        ) {
            $fkName = $this->foreignKeyName('wh_supply_returns', 'fa_organizational_unit_id');
            if (!$this->foreignKeyExists('wh_supply_returns', $fkName)) {
                Schema::table('wh_supply_returns', function (Blueprint $table) {
                    $table->foreign('fa_organizational_unit_id')
                        ->references('id')
                        ->on('fa_organizational_units')
                        ->restrictOnDelete();
                });
            }
        }

        Schema::dropIfExists('user_wh_office');
        Schema::dropIfExists('wh_offices');
    }

    public function down(): void
    {
        Schema::create('wh_offices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_wh_office', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wh_office_id')->constrained('wh_offices')->cascadeOnDelete();
            $table->unique(['user_id', 'wh_office_id']);
            $table->timestamps();
        });

        Schema::table('wh_supply_request', function (Blueprint $table) {
            $table->dropForeign(['fa_organizational_unit_id']);
            $table->dropColumn('fa_organizational_unit_id');
            $table->foreignId('office_id')->nullable()->constrained('wh_offices');
        });

        Schema::table('wh_supply_returns', function (Blueprint $table) {
            $table->dropForeign(['fa_organizational_unit_id']);
            $table->dropColumn('fa_organizational_unit_id');
            $table->foreignId('wh_office_id')->nullable()->constrained('wh_offices');
        });

        Schema::dropIfExists('user_fa_organizational_unit');
    }

    private function foreignKeyName(string $table, string $column): string
    {
        return "{$table}_{$column}_foreign";
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $database = Schema::getConnection()->getDatabaseName();

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};
