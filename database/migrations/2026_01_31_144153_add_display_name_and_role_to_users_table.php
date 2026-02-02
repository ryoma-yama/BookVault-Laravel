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

        // Add check constraint for role validation
        $this->createRoleValidationTriggers();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop triggers first
        $this->dropRoleValidationTriggers();

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'role']);
        });
    }

    /**
     * Create role validation triggers for the current database driver.
     */
    private function createRoleValidationTriggers(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: Create function and triggers
            DB::statement("
                CREATE OR REPLACE FUNCTION check_users_role()
                RETURNS TRIGGER AS $$
                BEGIN
                    IF NEW.role NOT IN ('admin', 'user') THEN
                        RAISE EXCEPTION 'Invalid role value';
                    END IF;
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            ");

            DB::statement("
                CREATE TRIGGER users_role_check_insert
                BEFORE INSERT ON users
                FOR EACH ROW
                EXECUTE FUNCTION check_users_role();
            ");

            DB::statement("
                CREATE TRIGGER users_role_check_update
                BEFORE UPDATE ON users
                FOR EACH ROW
                EXECUTE FUNCTION check_users_role();
            ");
        } elseif ($driver === 'sqlite') {
            // SQLite: Use SQLite-specific trigger syntax
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
        } else {
            throw new \RuntimeException("Unsupported database driver: {$driver}. Only PostgreSQL and SQLite are supported.");
        }
    }

    /**
     * Drop role validation triggers for the current database driver.
     */
    private function dropRoleValidationTriggers(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("DROP TRIGGER IF EXISTS users_role_check_insert ON users");
            DB::statement("DROP TRIGGER IF EXISTS users_role_check_update ON users");
            DB::statement("DROP FUNCTION IF EXISTS check_users_role()");
        } elseif ($driver === 'sqlite') {
            DB::statement("DROP TRIGGER IF EXISTS users_role_check_insert");
            DB::statement("DROP TRIGGER IF EXISTS users_role_check_update");
        }
        // No error thrown on down() to allow rollback even on unsupported drivers
    }
};
