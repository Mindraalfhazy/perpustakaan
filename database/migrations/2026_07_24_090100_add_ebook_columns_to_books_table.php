<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (!Schema::hasColumn('books', 'ebook_path')) {
                $table->string('ebook_path')->nullable()->after('tersedia');
            }

            if (!Schema::hasColumn('books', 'has_ebook')) {
                $table->boolean('has_ebook')->default(false)->after('ebook_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'has_ebook')) {
                $table->dropColumn('has_ebook');
            }

            if (Schema::hasColumn('books', 'ebook_path')) {
                $table->dropColumn('ebook_path');
            }
        });
    }
};
