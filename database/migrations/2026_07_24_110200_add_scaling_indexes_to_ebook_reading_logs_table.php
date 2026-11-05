<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ebook_reading_logs', function (Blueprint $table) {
            $table->index('user_id', 'ebook_logs_user_id_index');
            $table->index('book_id', 'ebook_logs_book_id_index');
            $table->index('opened_at', 'ebook_logs_opened_at_index');
            $table->index('last_read_at', 'ebook_logs_last_read_at_index');
            $table->index(['opened_at', 'book_id'], 'ebook_logs_opened_book_index');
            $table->index(['opened_at', 'user_id'], 'ebook_logs_opened_user_index');
        });
    }

    public function down(): void
    {
        Schema::table('ebook_reading_logs', function (Blueprint $table) {
            $table->dropIndex('ebook_logs_user_id_index');
            $table->dropIndex('ebook_logs_book_id_index');
            $table->dropIndex('ebook_logs_opened_at_index');
            $table->dropIndex('ebook_logs_last_read_at_index');
            $table->dropIndex('ebook_logs_opened_book_index');
            $table->dropIndex('ebook_logs_opened_user_index');
        });
    }
};
