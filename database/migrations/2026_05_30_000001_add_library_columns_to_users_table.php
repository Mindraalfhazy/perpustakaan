<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'siswa'])->default('siswa')->after('password');
            }

            if (!Schema::hasColumn('users', 'nisn')) {
                $table->string('nisn')->nullable()->after('role');
            }

            if (!Schema::hasColumn('users', 'kelas')) {
                $table->string('kelas')->nullable()->after('nisn');
            }
        });
    }

    public function down(): void
    {
        foreach (['kelas', 'nisn', 'role'] as $column) {
            if (!Schema::hasColumn('users', $column)) {
                continue;
            }

            Schema::table('users', function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });
        }
    }
};
