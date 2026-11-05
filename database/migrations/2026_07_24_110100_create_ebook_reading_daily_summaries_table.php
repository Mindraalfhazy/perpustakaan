<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ebook_reading_daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('summary_date');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kelas')->nullable();
            $table->string('book_category')->nullable();
            $table->unsignedInteger('total_sessions')->default(0);
            $table->unsignedInteger('total_duration_seconds')->default(0);
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['summary_date', 'user_id', 'book_id'], 'ebook_daily_summary_unique');
            $table->index(['summary_date', 'kelas'], 'ebook_daily_summary_date_class_index');
            $table->index(['summary_date', 'book_category'], 'ebook_daily_summary_date_category_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebook_reading_daily_summaries');
    }
};
