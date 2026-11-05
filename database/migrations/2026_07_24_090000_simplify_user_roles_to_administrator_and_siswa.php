<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','administrator','siswa') NOT NULL DEFAULT 'siswa'");
        } elseif (DB::getDriverName() === 'sqlite') {
            $this->rebuildSqliteUsersTable();
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('siswa')->change();
            });
        }

        DB::table('users')
            ->where('role', 'admin')
            ->update(['role' => 'administrator']);

        DB::table('users')
            ->whereNull('role')
            ->update(['role' => 'siswa']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('administrator','siswa') NOT NULL DEFAULT 'siswa'");
        }

        $this->mergePermissionRoles();
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','administrator','siswa') NOT NULL DEFAULT 'siswa'");
        } elseif (DB::getDriverName() === 'sqlite') {
            $this->rebuildSqliteUsersTable(true);
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('siswa')->change();
            });
        }

        DB::table('users')
            ->where('role', 'administrator')
            ->update(['role' => 'admin']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','siswa') NOT NULL DEFAULT 'siswa'");
        }
    }

    private function mergePermissionRoles(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $rolesTable = $tableNames['roles'] ?? 'roles';
        $modelHasRolesTable = $tableNames['model_has_roles'] ?? 'model_has_roles';
        $roleHasPermissionsTable = $tableNames['role_has_permissions'] ?? 'role_has_permissions';
        $rolePivotKey = $columnNames['role_pivot_key'] ?? PermissionRegistrar::$pivotRole;
        $permissionPivotKey = $columnNames['permission_pivot_key'] ?? PermissionRegistrar::$pivotPermission;
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';
        $teamsEnabled = (bool) config('permission.teams');
        $teamForeignKey = $columnNames['team_foreign_key'] ?? 'team_id';

        if (!Schema::hasTable($rolesTable)) {
            return;
        }

        $administratorId = $this->ensureRole($rolesTable, ['administrator', 'Administrator', 'admin', 'Admin'], 'administrator');
        $siswaId = $this->ensureRole($rolesTable, ['siswa', 'Siswa', 'anggota', 'Anggota'], 'siswa');

        $this->mergeRoleIds(
            $rolesTable,
            $modelHasRolesTable,
            $roleHasPermissionsTable,
            ['Administrator', 'admin', 'Admin', 'Pustakawan', 'pustakawan'],
            $administratorId,
            $rolePivotKey,
            $permissionPivotKey,
            $modelMorphKey,
            $teamsEnabled,
            $teamForeignKey
        );

        $this->mergeRoleIds(
            $rolesTable,
            $modelHasRolesTable,
            $roleHasPermissionsTable,
            ['Anggota', 'anggota', 'Siswa'],
            $siswaId,
            $rolePivotKey,
            $permissionPivotKey,
            $modelMorphKey,
            $teamsEnabled,
            $teamForeignKey
        );

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    private function rebuildSqliteUsersTable(bool $restoreAdminValue = false): void
    {
        $hasTwoFactorConfirmedAt = Schema::hasColumn('users', 'two_factor_confirmed_at');

        DB::statement('PRAGMA foreign_keys = OFF');

        Schema::dropIfExists('users_rebuild');

        Schema::create('users_rebuild', function (Blueprint $table) use ($hasTwoFactorConfirmedAt) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();

            if ($hasTwoFactorConfirmedAt) {
                $table->timestamp('two_factor_confirmed_at')->nullable();
            }

            $table->string('role')->default('siswa');
            $table->string('nisn')->nullable();
            $table->string('kelas')->nullable();
            $table->rememberToken();
            $table->string('created_by')->nullable()->default('System');
            $table->string('updated_by')->nullable()->default('System');
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $columns = [
            'id',
            'name',
            'email',
            'email_verified_at',
            'password',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];

        if ($hasTwoFactorConfirmedAt) {
            $columns[] = 'two_factor_confirmed_at';
        }

        $columns = array_merge($columns, [
            'role',
            'nisn',
            'kelas',
            'remember_token',
            'created_by',
            'updated_by',
            'deleted_by',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $roleExpression = $restoreAdminValue
            ? "CASE WHEN role = 'administrator' THEN 'admin' WHEN role IS NULL THEN 'siswa' ELSE role END"
            : "CASE WHEN role = 'admin' THEN 'administrator' WHEN role IS NULL THEN 'siswa' ELSE role END";

        $insertColumns = collect($columns)
            ->map(fn (string $column) => $column === 'role' ? "{$roleExpression} as role" : $column)
            ->implode(', ');

        DB::statement(sprintf(
            'INSERT INTO users_rebuild (%s) SELECT %s FROM users',
            implode(', ', $columns),
            $insertColumns
        ));

        Schema::drop('users');
        Schema::rename('users_rebuild', 'users');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    private function ensureRole(string $rolesTable, array $candidates, string $targetName): int
    {
        $role = DB::table($rolesTable)
            ->whereIn('name', $candidates)
            ->orderByRaw('CASE WHEN name = ? THEN 0 ELSE 1 END', [$targetName])
            ->first();

        if ($role) {
            DB::table($rolesTable)
                ->where('id', $role->id)
                ->update([
                    'name' => $targetName,
                    'guard_name' => 'web',
                ]);

            return (int) $role->id;
        }

        return (int) DB::table($rolesTable)->insertGetId([
            'name' => $targetName,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function mergeRoleIds(
        string $rolesTable,
        string $modelHasRolesTable,
        string $roleHasPermissionsTable,
        array $sourceNames,
        int $targetRoleId,
        string $rolePivotKey,
        string $permissionPivotKey,
        string $modelMorphKey,
        bool $teamsEnabled,
        string $teamForeignKey
    ): void {
        $sourceRoleIds = DB::table($rolesTable)
            ->whereIn('name', $sourceNames)
            ->where('id', '!=', $targetRoleId)
            ->pluck('id');

        foreach ($sourceRoleIds as $sourceRoleId) {
            if (Schema::hasTable($modelHasRolesTable)) {
                $modelRoleRows = DB::table($modelHasRolesTable)
                    ->where($rolePivotKey, $sourceRoleId)
                    ->get();

                foreach ($modelRoleRows as $row) {
                    $payload = [
                        $rolePivotKey => $targetRoleId,
                        'model_type' => $row->model_type,
                        $modelMorphKey => $row->{$modelMorphKey},
                    ];

                    if ($teamsEnabled && isset($row->{$teamForeignKey})) {
                        $payload[$teamForeignKey] = $row->{$teamForeignKey};
                    }

                    DB::table($modelHasRolesTable)->insertOrIgnore($payload);
                }

                DB::table($modelHasRolesTable)
                    ->where($rolePivotKey, $sourceRoleId)
                    ->delete();
            }

            if (Schema::hasTable($roleHasPermissionsTable)) {
                $permissionRows = DB::table($roleHasPermissionsTable)
                    ->where($rolePivotKey, $sourceRoleId)
                    ->get();

                foreach ($permissionRows as $row) {
                    DB::table($roleHasPermissionsTable)->insertOrIgnore([
                        $permissionPivotKey => $row->{$permissionPivotKey},
                        $rolePivotKey => $targetRoleId,
                    ]);
                }

                DB::table($roleHasPermissionsTable)
                    ->where($rolePivotKey, $sourceRoleId)
                    ->delete();
            }

            DB::table($rolesTable)
                ->where('id', $sourceRoleId)
                ->delete();
        }
    }
};
