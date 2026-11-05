<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('books', 'title') && !Schema::hasColumn('books', 'judul')) {
            Schema::table('books', function (Blueprint $table) {
                $table->renameColumn('title', 'judul');
            });
        }

        if (Schema::hasColumn('books', 'author') && !Schema::hasColumn('books', 'penulis')) {
            Schema::table('books', function (Blueprint $table) {
                $table->renameColumn('author', 'penulis');
            });
        }

        if (Schema::hasColumn('books', 'publish_year') && !Schema::hasColumn('books', 'tahun')) {
            Schema::table('books', function (Blueprint $table) {
                $table->renameColumn('publish_year', 'tahun');
            });
        }

        Schema::table('books', function (Blueprint $table) {
            if (!Schema::hasColumn('books', 'tahun')) {
                $table->year('tahun')->nullable()->after('penulis');
            }

            if (!Schema::hasColumn('books', 'kategori')) {
                $table->enum('kategori', ['Pelajaran', 'Fiksi', 'Non-Fiksi', 'Referensi', 'Lainnya'])
                    ->default('Lainnya')
                    ->after('tahun');
            }

            if (!Schema::hasColumn('books', 'stok')) {
                $table->integer('stok')->default(1)->after('kategori');
            }

            if (!Schema::hasColumn('books', 'tersedia')) {
                $table->integer('tersedia')->default(1)->after('stok');
            }
        });

        DB::table('books')
            ->whereNull('tahun')
            ->update(['tahun' => 2024]);

        DB::table('books')
            ->whereNull('kategori')
            ->update(['kategori' => 'Lainnya']);

        DB::table('books')
            ->where('stok', '<', 1)
            ->orWhereNull('stok')
            ->update(['stok' => 1]);

        DB::table('books')
            ->where('tersedia', '<', 0)
            ->orWhereNull('tersedia')
            ->update(['tersedia' => DB::raw('stok')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('books', 'kategori')) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropColumn('kategori');
            });
        }

        if (Schema::hasColumn('books', 'stok')) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropColumn('stok');
            });
        }

        if (Schema::hasColumn('books', 'tersedia')) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropColumn('tersedia');
            });
        }

        if (Schema::hasColumn('books', 'judul') && !Schema::hasColumn('books', 'title')) {
            Schema::table('books', function (Blueprint $table) {
                $table->renameColumn('judul', 'title');
            });
        }

        if (Schema::hasColumn('books', 'penulis') && !Schema::hasColumn('books', 'author')) {
            Schema::table('books', function (Blueprint $table) {
                $table->renameColumn('penulis', 'author');
            });
        }

 if (Schema::hasColumn('books', 'publish_year') && !Schema::hasColumn('books', 'tahun')) {
    DB::statement("ALTER TABLE books MODIFY publish_year YEAR NULL");
    Schema::table('books', function (Blueprint $table) {
        $table->renameColumn('publish_year', 'tahun');
    });
}
    }
};
