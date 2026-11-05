<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const GURU_USER_ID = 566;

    public function up(): void
    {
        DB::transaction(function () {
            $memberIds = $this->getMemberIdsForGuruUser();
            $borrowIds = $this->getBorrowIdsForGuruUser();

            $this->deleteBorrowBookRelations($borrowIds);
            $this->deleteSpatieRelations();
            $this->deleteMorphRelations();
            $this->deleteDirectUserRelations();
            $this->deleteOtherUserRelations();
            $this->deleteLoanRelations($memberIds);
            $this->detachMemberRelations();
            $this->softDeleteGuruUser();
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('administrator','siswa') NOT NULL DEFAULT 'siswa'");

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('chatbot_histories');
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('administrator','siswa','guru') NOT NULL DEFAULT 'siswa'");

        if (Schema::hasTable('chatbot_histories')) {
            return;
        }

        Schema::create('chatbot_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->text('message');
            $table->text('reply');
            $table->string('intent')->default('fallback');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['member_id', 'created_at']);
        });
    }

    private function deleteBorrowBookRelations(array $borrowIds): void
    {
        if ($borrowIds === [] || !Schema::hasTable('borrow_books')) {
            return;
        }

        DB::table('borrow_books')
            ->whereIn('borrow_id', $borrowIds)
            ->delete();
    }

    private function deleteSpatieRelations(): void
    {
        $tableNames = config('permission.table_names', []);
        $columnNames = config('permission.column_names', []);
        $modelHasRolesTable = $tableNames['model_has_roles'] ?? 'model_has_roles';
        $modelHasPermissionsTable = $tableNames['model_has_permissions'] ?? 'model_has_permissions';
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';

        if (Schema::hasTable($modelHasRolesTable)) {
            DB::table($modelHasRolesTable)
                ->where('model_type', User::class)
                ->where($modelMorphKey, self::GURU_USER_ID)
                ->delete();
        }

        if (Schema::hasTable($modelHasPermissionsTable)) {
            DB::table($modelHasPermissionsTable)
                ->where('model_type', User::class)
                ->where($modelMorphKey, self::GURU_USER_ID)
                ->delete();
        }
    }

    private function deleteMorphRelations(): void
    {
        if (Schema::hasTable('personal_access_tokens')) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->where('tokenable_id', self::GURU_USER_ID)
                ->delete();
        }

        if (Schema::hasTable('notifications')) {
            DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', self::GURU_USER_ID)
                ->delete();
        }
    }

    private function deleteDirectUserRelations(): void
    {
        foreach ([
            'borrows',
            'ebook_reading_logs',
            'ebook_reading_daily_summaries',
            'chatbot_histories',
        ] as $table) {
            $this->deleteRowsByUserId($table);
        }

        $this->nullifyUserId('cards');
    }

    private function deleteOtherUserRelations(): void
    {
        $handledTables = [
            'users',
            'members',
            'cards',
            'borrows',
            'ebook_reading_logs',
            'ebook_reading_daily_summaries',
            'chatbot_histories',
        ];

        foreach ($this->discoverUserIdTables() as $table) {
            if (in_array($table, $handledTables, true)) {
                continue;
            }

            $this->deleteRowsByUserId($table);
        }
    }

    private function deleteLoanRelations(array $memberIds): void
    {
        if ($memberIds === [] || !Schema::hasTable('loans') || !Schema::hasColumn('loans', 'member_id')) {
            return;
        }

        DB::table('loans')
            ->whereIn('member_id', $memberIds)
            ->delete();
    }

    private function detachMemberRelations(): void
    {
        if (!Schema::hasTable('members') || !Schema::hasColumn('members', 'user_id')) {
            return;
        }

        $payload = ['user_id' => null];

        if (Schema::hasColumn('members', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('members')
            ->where('user_id', self::GURU_USER_ID)
            ->update($payload);
    }

    private function softDeleteGuruUser(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $payload = [
            // Soft-deleted row tetap harus memakai nilai enum yang valid
            // sebelum opsi "guru" dihapus dari kolom role.
            'role' => 'siswa',
            'deleted_at' => now(),
        ];

        if (Schema::hasColumn('users', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        if (Schema::hasColumn('users', 'updated_by')) {
            $payload['updated_by'] = 'migration';
        }

        if (Schema::hasColumn('users', 'deleted_by')) {
            $payload['deleted_by'] = 'migration';
        }

        DB::table('users')
            ->where('id', self::GURU_USER_ID)
            ->update($payload);
    }

    private function deleteRowsByUserId(string $table): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'user_id')) {
            return;
        }

        DB::table($table)
            ->where('user_id', self::GURU_USER_ID)
            ->delete();
    }

    private function nullifyUserId(string $table): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'user_id')) {
            return;
        }

        $payload = ['user_id' => null];

        if (Schema::hasColumn($table, 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table($table)
            ->where('user_id', self::GURU_USER_ID)
            ->update($payload);
    }

    private function getMemberIdsForGuruUser(): array
    {
        if (!Schema::hasTable('members') || !Schema::hasColumn('members', 'user_id')) {
            return [];
        }

        return DB::table('members')
            ->where('user_id', self::GURU_USER_ID)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function getBorrowIdsForGuruUser(): array
    {
        if (!Schema::hasTable('borrows') || !Schema::hasColumn('borrows', 'user_id')) {
            return [];
        }

        return DB::table('borrows')
            ->where('user_id', self::GURU_USER_ID)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function discoverUserIdTables(): array
    {
        if (DB::getDriverName() !== 'mysql') {
            return [];
        }

        return DB::table('information_schema.columns')
            ->selectRaw('TABLE_NAME as discovered_table_name')
            ->where('table_schema', DB::getDatabaseName())
            ->where('column_name', 'user_id')
            ->get()
            ->map(function ($row) {
                return (string) ($row->discovered_table_name ?? $row->TABLE_NAME ?? '');
            })
            ->filter()
            ->values()
            ->all();
    }
};
