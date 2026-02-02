<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('two_factor_confirmed_at');
            $table->string('role')->default('user')->after('display_name');
        });

        // Add check constraint for role validation (SQLite 3.37.0+ supports CHECK constraints)
        DB::statement("CREATE TRIGGER users_role_check_insert BEFORE INSERT ON users
            BEGIN
                SELECT CASE
                    WHEN NEW.role NOT IN ('admin', 'user') THEN
                        RAISE (ABORT, 'Invalid role value')
                END;
            END;");

        DB::statement("CREATE TRIGGER users_role_check_update BEFORE UPDATE ON users
            BEGIN
                SELECT CASE
                    WHEN NEW.role NOT IN ('admin', 'user') THEN
                        RAISE (ABORT, 'Invalid role value')
                END;
            END;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop triggers first
        DB::statement("DROP TRIGGER IF EXISTS users_role_check_insert");
        DB::statement("DROP TRIGGER IF EXISTS users_role_check_update");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'role']);
        });
    }
};
