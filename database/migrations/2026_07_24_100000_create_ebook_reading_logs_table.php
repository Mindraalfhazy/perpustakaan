<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ebook_reading_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->timestamp('opened_at')->nullable()->index();
            $table->timestamp('last_read_at')->nullable()->index();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedInteger('last_page_read')->default(1);
            $table->string('device_type', 20)->default('desktop');
            $table->timestamps();

            $table->index(['book_id', 'opened_at']);
            $table->index(['user_id', 'opened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebook_reading_logs');
    }
};
