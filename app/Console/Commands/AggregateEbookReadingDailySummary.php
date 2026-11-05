<?php

namespace App\Console\Commands;

use App\Models\EbookReadingLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AggregateEbookReadingDailySummary extends Command
{
    protected $signature = 'ebook-reading:aggregate-daily {--date=}';

    protected $description = 'Mengagregasi log baca e-book harian ke tabel ringkasan';

    public function handle(): int
    {
        $targetDate = $this->option('date')
            ? now()->parse($this->option('date'))->toDateString()
            : now()->subDay()->toDateString();

        $rows = EbookReadingLog::query()
            ->selectRaw('DATE(ebook_reading_logs.opened_at) as summary_date')
            ->selectRaw('ebook_reading_logs.user_id')
            ->selectRaw('ebook_reading_logs.book_id')
            ->selectRaw('COALESCE(users.kelas, members.kelas) as kelas')
            ->selectRaw('books.kategori as book_category')
            ->selectRaw('COUNT(*) as total_sessions')
            ->selectRaw('SUM(ebook_reading_logs.duration_seconds) as total_duration_seconds')
            ->selectRaw('MAX(ebook_reading_logs.last_read_at) as last_read_at')
            ->leftJoin('users', 'users.id', '=', 'ebook_reading_logs.user_id')
            ->leftJoin('members', 'members.user_id', '=', 'users.id')
            ->leftJoin('books', 'books.id', '=', 'ebook_reading_logs.book_id')
            ->whereDate('ebook_reading_logs.opened_at', $targetDate)
            ->groupBy(
                DB::raw('DATE(ebook_reading_logs.opened_at)'),
                'ebook_reading_logs.user_id',
                'ebook_reading_logs.book_id',
                DB::raw('COALESCE(users.kelas, members.kelas)'),
                'books.kategori'
            )
            ->get();

        foreach ($rows as $row) {
            DB::table('ebook_reading_daily_summaries')->updateOrInsert(
                [
                    'summary_date' => $row->summary_date,
                    'user_id' => $row->user_id,
                    'book_id' => $row->book_id,
                ],
                [
                    'kelas' => $row->kelas,
                    'book_category' => $row->book_category,
                    'total_sessions' => (int) $row->total_sessions,
                    'total_duration_seconds' => (int) $row->total_duration_seconds,
                    'last_read_at' => $row->last_read_at,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->info("Ringkasan baca {$targetDate} berhasil diperbarui: {$rows->count()} baris.");

        return self::SUCCESS;
    }
}
