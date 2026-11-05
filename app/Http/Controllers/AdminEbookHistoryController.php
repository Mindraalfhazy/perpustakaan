<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\EbookReadingLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class AdminEbookHistoryController extends Controller
{
    public function index(Request $request): View
    {
        [$dateFrom, $dateTo, $range, $rangeLabel] = $this->resolveDateRange($request);
        $logsQuery = $this->filteredLogsQuery($request, $dateFrom, $dateTo);

        $logs = (clone $logsQuery)
            ->latest('opened_at')
            ->paginate(10)
            ->withQueryString();

        $popularBooks = (clone $logsQuery)
            ->select('book_id')
            ->selectRaw('COUNT(*) as total_sessions')
            ->selectRaw('SUM(duration_seconds) as total_duration')
            ->with('book')
            ->groupBy('book_id')
            ->orderByDesc('total_sessions')
            ->orderByDesc('total_duration')
            ->limit(5)
            ->get();

        $activityTrend = (clone $logsQuery)
            ->selectRaw('DATE(opened_at) as activity_date')
            ->selectRaw('COUNT(*) as total_sessions')
            ->groupBy(DB::raw('DATE(opened_at)'))
            ->orderBy('activity_date')
            ->get();

        $activeReaders = (clone $logsQuery)
            ->select('user_id')
            ->selectRaw('COUNT(*) as total_sessions')
            ->selectRaw('SUM(duration_seconds) as total_duration')
            ->with('user')
            ->groupBy('user_id')
            ->orderByDesc('total_sessions')
            ->orderByDesc('total_duration')
            ->limit(5)
            ->get();

        $mostActiveDay = (clone $logsQuery)
            ->selectRaw('DATE(opened_at) as activity_date')
            ->selectRaw('COUNT(*) as total_sessions')
            ->groupBy(DB::raw('DATE(opened_at)'))
            ->orderByDesc('total_sessions')
            ->orderBy('activity_date')
            ->first();

        $totalSessions = (clone $logsQuery)->count();
        $uniqueReaders = (clone $logsQuery)->distinct('user_id')->count('user_id');
        $totalDurationSeconds = (int) ((clone $logsQuery)->sum('duration_seconds') ?? 0);
        $averageDurationSeconds = $totalSessions > 0 ? (int) floor($totalDurationSeconds / $totalSessions) : 0;

        $popularBooks->each(fn ($item) => $item->display_duration = $this->formatDuration((int) $item->total_duration));
        $activeReaders->each(fn ($item) => $item->display_duration = $this->formatDuration((int) $item->total_duration));

        return view('admin.ebooks.history', [
            'title' => 'History Pembaca E-Book',
            'breadcrumb' => ['Dashboard', 'History E-Book'],
            'logs' => $logs,
            'filters' => [
                'student' => trim($request->string('student')->toString()),
                'book' => trim($request->string('book')->toString()),
                'kelas' => $request->string('kelas')->toString(),
                'category' => $request->string('category')->toString(),
                'date_from' => $dateFrom?->toDateString(),
                'date_to' => $dateTo?->toDateString(),
                'range' => $range,
            ],
            'rangeLabel' => $rangeLabel,
            'kelasOptions' => User::query()
                ->whereNotNull('kelas')
                ->distinct()
                ->orderBy('kelas')
                ->pluck('kelas'),
            'categoryOptions' => Book::query()
                ->whereNotNull('kategori')
                ->distinct()
                ->orderBy('kategori')
                ->pluck('kategori'),
            'summary' => [
                'total_sessions' => $totalSessions,
                'unique_readers' => $uniqueReaders,
                'total_duration' => $this->formatDuration($totalDurationSeconds),
                'average_duration' => $this->formatDuration($averageDurationSeconds),
                'most_active_day' => $mostActiveDay
                    ? Carbon::parse($mostActiveDay->activity_date)->translatedFormat('d F Y')
                    : '-',
                'most_active_day_total' => (int) ($mostActiveDay->total_sessions ?? 0),
                'top_book' => $popularBooks->first()?->book?->judul ?? '-',
            ],
            'chartData' => [
                'popularBooks' => [
                    'labels' => $popularBooks->map(fn ($log) => str($log->book?->judul ?? 'Tidak diketahui')->limit(28)->toString())->values(),
                    'sessions' => $popularBooks->pluck('total_sessions')->map(fn ($value) => (int) $value)->values(),
                ],
                'activityTrend' => [
                    'labels' => $activityTrend->map(fn ($item) => Carbon::parse($item->activity_date)->translatedFormat('d M'))->values(),
                    'sessions' => $activityTrend->pluck('total_sessions')->map(fn ($value) => (int) $value)->values(),
                ],
                'activeReaders' => [
                    'labels' => $activeReaders->map(fn ($log) => str($log->user?->name ?? 'Siswa')->limit(24)->toString())->values(),
                    'sessions' => $activeReaders->pluck('total_sessions')->map(fn ($value) => (int) $value)->values(),
                ],
            ],
            'highlights' => [
                'popularBooks' => $popularBooks,
                'activeReaders' => $activeReaders,
            ],
            'reportPeriods' => $this->reportPeriods(),
        ]);
    }

    public function print(Request $request): View
    {
        $data = $this->buildReportData($request);

        return view('admin.ebooks.report', $data + ['isPdf' => false]);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->buildReportData($request);
        $pdf = Pdf::loadView('admin.ebooks.report', $data + ['isPdf' => true])
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-riwayat-ebook-' . now()->format('Ymd-His') . '.pdf');
    }

    public function exportCsv(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request, 'report_period');
        $query = $this->filteredLogsQuery($request, $dateFrom, $dateTo)->latest('opened_at');

        return Response::streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Nama Siswa', 'Kelas', 'Judul E-Book', 'Kategori', 'Dibuka', 'Terakhir Dibaca', 'Durasi', 'Halaman Terakhir', 'Perangkat']);

            foreach ($query->cursor() as $log) {
                fputcsv($handle, [
                    $log->user?->name,
                    $log->user?->displayClass(),
                    $log->book?->judul,
                    $log->book?->kategori,
                    optional($log->opened_at)->format('d-m-Y H:i'),
                    optional($log->last_read_at)->format('d-m-Y H:i'),
                    $log->duration_label,
                    $log->last_page_read,
                    ucfirst((string) $log->device_type),
                ]);
            }

            fclose($handle);
        }, 'laporan-riwayat-ebook-' . now()->format('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildReportData(Request $request): array
    {
        [$dateFrom, $dateTo, $period, $periodLabel] = $this->resolveDateRange($request, 'report_period');
        $query = $this->filteredLogsQuery($request, $dateFrom, $dateTo);
        $logs = (clone $query)->latest('opened_at')->get();

        $topBook = (clone $query)
            ->select('book_id')
            ->selectRaw('COUNT(*) as total_sessions')
            ->with('book')
            ->groupBy('book_id')
            ->orderByDesc('total_sessions')
            ->first();

        $topStudent = (clone $query)
            ->select('user_id')
            ->selectRaw('COUNT(*) as total_sessions')
            ->with('user')
            ->groupBy('user_id')
            ->orderByDesc('total_sessions')
            ->first();

        $topClass = (clone $query)
            ->leftJoin('users', 'users.id', '=', 'ebook_reading_logs.user_id')
            ->selectRaw('COALESCE(users.kelas, "-") as kelas')
            ->selectRaw('COUNT(*) as total_sessions')
            ->groupBy(DB::raw('COALESCE(users.kelas, "-")'))
            ->orderByDesc('total_sessions')
            ->first();

        return [
            'title' => 'Laporan Riwayat Baca E-Book',
            'breadcrumb' => ['Dashboard', 'History E-Book', 'Cetak Laporan'],
            'logs' => $logs,
            'filters' => $this->normalizedFilters($request, $dateFrom, $dateTo, $period),
            'periodLabel' => $periodLabel,
            'generatedAt' => now(),
            'generatedBy' => $request->user(),
            'reportTitle' => 'Laporan Riwayat Baca E-Book - ' . $periodLabel,
            'summary' => [
                'total_sessions' => $logs->count(),
                'unique_readers' => $logs->pluck('user_id')->filter()->unique()->count(),
                'top_book' => $topBook?->book?->judul ?? '-',
                'top_student' => $topStudent?->user?->name ?? '-',
                'top_class' => $topClass?->kelas ?? '-',
            ],
            'logoPath' => public_path('logo/kemenag.png'),
        ];
    }

    private function filteredLogsQuery(Request $request, ?Carbon $dateFrom, ?Carbon $dateTo): Builder
    {
        $student = trim($request->string('student')->toString());
        $book = trim($request->string('book')->toString());
        $kelas = $request->string('kelas')->toString();
        $category = $request->string('category')->toString();

        return EbookReadingLog::query()
            ->with(['user', 'book'])
            ->when($student, function (Builder $query) use ($student) {
                $query->whereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$student}%"));
            })
            ->when($book, function (Builder $query) use ($book) {
                $query->whereHas('book', fn (Builder $bookQuery) => $bookQuery->where('judul', 'like', "%{$book}%"));
            })
            ->when($kelas, function (Builder $query) use ($kelas) {
                $query->whereHas('user', fn (Builder $userQuery) => $userQuery->where('kelas', $kelas));
            })
            ->when($category, function (Builder $query) use ($category) {
                $query->whereHas('book', fn (Builder $bookQuery) => $bookQuery->where('kategori', $category));
            })
            ->when($dateFrom, fn (Builder $query) => $query->where('opened_at', '>=', $dateFrom->copy()->startOfDay()))
            ->when($dateTo, fn (Builder $query) => $query->where('opened_at', '<=', $dateTo->copy()->endOfDay()));
    }

    private function resolveDateRange(Request $request, string $key = 'range'): array
    {
        $range = $request->string($key)->toString() ?: ($key === 'report_period' ? 'monthly' : 'this_month');
        $dateFromInput = $request->string('date_from')->toString();
        $dateToInput = $request->string('date_to')->toString();
        $today = now()->startOfDay();

        if (in_array($range, ['custom', 'range_custom'], true) && $dateFromInput && $dateToInput) {
            return [Carbon::parse($dateFromInput), Carbon::parse($dateToInput), 'custom', 'Rentang Kustom'];
        }

        return match ($range) {
            'today', 'daily' => [$today->copy(), $today->copy(), $range, 'Hari Ini'],
            'this_week', 'weekly' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek(), $range, 'Minggu Ini'],
            'this_year', 'yearly' => [$today->copy()->startOfYear(), $today->copy()->endOfYear(), $range, 'Tahun ' . $today->year],
            '7d' => [$today->copy()->subDays(6), $today->copy(), '7d', '7 Hari Terakhir'],
            '30d' => [$today->copy()->subDays(29), $today->copy(), '30d', '30 Hari Terakhir'],
            '90d' => [$today->copy()->subDays(89), $today->copy(), '90d', '90 Hari Terakhir'],
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth(), $range, 'Bulan ' . $today->translatedFormat('F Y')],
        };
    }

    private function normalizedFilters(Request $request, ?Carbon $dateFrom, ?Carbon $dateTo, string $range): array
    {
        return [
            'student' => trim($request->string('student')->toString()),
            'book' => trim($request->string('book')->toString()),
            'kelas' => $request->string('kelas')->toString(),
            'category' => $request->string('category')->toString(),
            'date_from' => $dateFrom?->toDateString(),
            'date_to' => $dateTo?->toDateString(),
            'range' => $range,
        ];
    }

    private function reportPeriods(): Collection
    {
        return collect([
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan',
            'custom' => 'Rentang Kustom',
        ]);
    }

    private function formatDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);

        if ($seconds < 60) {
            return $seconds . ' detik';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours === 0) {
            return $minutes . ' menit';
        }

        return $hours . ' jam ' . $minutes . ' menit';
    }
}
