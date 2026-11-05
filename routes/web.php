<?php

use App\Http\Controllers\AdminCardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\AdminEbookHistoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EbookController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LocalizationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SiswaBookController;
use App\Http\Controllers\SiswaDashboardController;
use App\Http\Controllers\SiswaLoanController;
use App\Http\Controllers\SystemSettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->homeRoute());
});

Route::get('/home', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->homeRoute());
})->name('home');

Route::middleware(['auth', 'role:administrator'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:administrator'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('books', BookController::class);
    Route::get('ebooks/history', [AdminEbookHistoryController::class, 'index'])->name('ebooks.history');
    Route::get('ebooks/history/print', [AdminEbookHistoryController::class, 'print'])->name('ebooks.history.print');
    Route::get('ebooks/history/export-pdf', [AdminEbookHistoryController::class, 'exportPdf'])->name('ebooks.history.export-pdf');
    Route::get('ebooks/history/export-csv', [AdminEbookHistoryController::class, 'exportCsv'])->name('ebooks.history.export-csv');
    Route::post('books/{book}/upload-ebook', [BookController::class, 'uploadEbook'])->name('books.upload-ebook');
    Route::delete('books/{book}/ebook', [BookController::class, 'destroyEbook'])->name('books.destroy-ebook');
    Route::resource('members', MemberController::class);
    Route::resource('loans', LoanController::class);
    Route::post('loans/{loan}/return', [LoanController::class, 'processReturn'])->name('loans.return');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export-pdf', [ReportController::class, 'exportPdf'])
    ->name('reports.exportPdf');
    Route::get('settings', [SystemSettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SystemSettingController::class, 'update'])->name('settings.update');

    // Kartu Anggota (penerbitan kartu per siswa / per kelas)
    Route::get('kartu-anggota', [AdminCardController::class, 'index'])->name('cards.index');
    Route::post('kartu-anggota/{member}/terbitkan', [AdminCardController::class, 'issue'])->name('cards.issue');
    Route::post('kartu-anggota/terbitkan-massal', [AdminCardController::class, 'bulkIssue'])->name('cards.bulk-issue');
});

Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
    Route::get('books', [SiswaBookController::class, 'index'])->name('books.index');
    Route::get('loans', [SiswaLoanController::class, 'index'])->name('loans.index');
    Route::get('loans/create', [SiswaLoanController::class, 'create'])->name('loans.create');
    Route::post('loans', [SiswaLoanController::class, 'store'])->name('loans.store');
    Route::post('loans/{loan}/return', [SiswaLoanController::class, 'processReturn'])->name('loans.return');
    Route::get('kartu-anggota', [CardController::class, 'download'])->name('card.download');
});

Route::middleware(['auth', 'role:siswa'])->group(function () {
    Route::get('ebooks', [EbookController::class, 'index'])->name('ebooks.index');
    Route::get('ebooks/{book}/read', [EbookController::class, 'read'])->name('ebooks.read');
    Route::get('ebooks/{book}/file', [EbookController::class, 'file'])->name('ebooks.file');
    Route::post('ebooks/{book}/logs/{ebookReadingLog}', [EbookController::class, 'updateReadingProgress'])->name('ebooks.progress');
});

Route::middleware(['auth'])->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');
    Route::post('chatbot/send', [ChatbotController::class, 'send'])->name('chatbot.send');
    Route::post('chatbot/clear', [ChatbotController::class, 'clearHistory'])->name('chatbot.clear');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
});

Route::get('lang/{locale}', LocalizationController::class)->name('lang');
