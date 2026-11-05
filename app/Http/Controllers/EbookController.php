<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\EbookReadingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class EbookController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $kategori = $request->string('kategori')->toString();

        $books = Book::query()
            ->where('has_ebook', true)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%");
                });
            })
            ->when($kategori && $kategori !== 'Semua', fn ($query) => $query->where('kategori', $kategori))
            ->orderBy('judul')
            ->paginate(9)
            ->withQueryString();

        return view('siswa.ebooks.index', [
            'title' => 'E-Book',
            'breadcrumb' => ['E-Book'],
            'books' => $books,
            'search' => $search,
            'kategori' => $kategori ?: 'Semua',
            'kategoriOptions' => ['Semua', 'Pelajaran', 'Fiksi', 'Non-Fiksi', 'Referensi', 'Lainnya'],
        ]);
    }

    public function read(Book $book): View
    {
        abort_unless($book->has_ebook && $book->ebook_path, Response::HTTP_NOT_FOUND);

        $user = auth()->user();
        $resumePage = EbookReadingLog::query()
            ->where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->latest('last_read_at')
            ->value('last_page_read') ?? 1;

        $readingLog = EbookReadingLog::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'opened_at' => now(),
            'last_read_at' => now(),
            'duration_seconds' => 0,
            'device_type' => $this->resolveDeviceType(request()),
            'last_page_read' => max(1, (int) $resumePage),
        ]);

        return view('siswa.ebooks.read', [
            'title' => 'Baca E-Book',
            'breadcrumb' => ['E-Book', $book->judul],
            'book' => $book,
            'pdfUrl' => route('ebooks.file', $book),
            'readerUserId' => auth()->id(),
            'readerLogId' => $readingLog->id,
            'readerResumePage' => max(1, (int) $resumePage),
            'readerProgressUrl' => route('ebooks.progress', [$book, $readingLog]),
        ]);
    }

    public function updateReadingProgress(Request $request, Book $book, EbookReadingLog $ebookReadingLog): JsonResponse
    {
        abort_unless($ebookReadingLog->book_id === $book->id, Response::HTTP_NOT_FOUND);
        abort_unless($ebookReadingLog->user_id === (int) $request->user()->id, Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'scale' => ['nullable', 'numeric', 'min:0.5', 'max:4'],
            'event' => ['nullable', 'string', 'max:30'],
        ]);

        $now = now();
        $lastReadAt = $ebookReadingLog->last_read_at ?? $ebookReadingLog->opened_at ?? $now;
        $durationIncrement = max(0, min(120, $lastReadAt->diffInSeconds($now)));

        $ebookReadingLog->forceFill([
            'last_read_at' => $now,
            'last_page_read' => max(1, (int) ($validated['page'] ?? $ebookReadingLog->last_page_read ?? 1)),
            'duration_seconds' => (int) $ebookReadingLog->duration_seconds + $durationIncrement,
        ])->save();

        return response()->json([
            'status' => 'ok',
            'log_id' => $ebookReadingLog->id,
            'last_page_read' => $ebookReadingLog->last_page_read,
            'duration_seconds' => $ebookReadingLog->duration_seconds,
        ]);
    }

    public function file(Request $request, Book $book): Response
    {
        abort_unless($book->has_ebook && $book->ebook_path, Response::HTTP_NOT_FOUND);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($book->ebook_path), Response::HTTP_NOT_FOUND);

        $content = $disk->get($book->ebook_path);
        $fileSize = strlen($content);
        $start = 0;
        $end = $fileSize - 1;
        $status = Response::HTTP_OK;
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($book->ebook_path) . '"',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($rangeHeader = $request->header('Range')) {
            if (preg_match('/bytes=(\d*)-(\d*)/', $rangeHeader, $matches)) {
                $start = $matches[1] === '' ? 0 : (int) $matches[1];
                $end = $matches[2] === '' ? $fileSize - 1 : (int) $matches[2];
                $end = min($end, $fileSize - 1);

                if ($start <= $end) {
                    $content = substr($content, $start, ($end - $start) + 1);
                    $status = Response::HTTP_PARTIAL_CONTENT;
                    $headers['Content-Range'] = sprintf('bytes %d-%d/%d', $start, $end, $fileSize);
                }
            }
        }

        $headers['Content-Length'] = (string) strlen($content);

        return response($content, $status, $headers);
    }

    private function resolveDeviceType(Request $request): string
    {
        return preg_match('/mobile|android|iphone|ipad/i', (string) $request->userAgent()) === 1
            ? 'mobile'
            : 'desktop';
    }
}
