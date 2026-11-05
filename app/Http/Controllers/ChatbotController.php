<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ChatbotController extends Controller
{
    /**
     * Maksimal percakapan yang disimpan di session (bukan database).
     */
    private const HISTORY_LIMIT = 50;

    private const SESSION_HISTORY = 'chatbot_history';

    private const SESSION_LAST_SENT = 'chatbot_last_sent';

    public function __construct(private readonly ChatbotService $chatbotService)
    {
    }

    public function index(): View
    {
        $this->ensureSiswa();

        $history = collect(session(self::SESSION_HISTORY, []));

        return view('chatbot.index', [
            'title' => 'Chatbot',
            'breadcrumb' => ['Chatbot'],
            'history' => $history,
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $this->ensureSiswa();

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = trim($validated['message']);

        if ($message === '') {
            throw ValidationException::withMessages([
                'message' => 'Pesan tidak boleh kosong.',
            ]);
        }

        // Anti-spam sederhana: jeda minimal 2 detik antar pesan.
        $now = now()->timestamp;

        if ($now - (int) session(self::SESSION_LAST_SENT, 0) < 2) {
            return redirect()
                ->route('chatbot.index')
                ->with('error', 'Tunggu sebentar sebelum mengirim pesan lagi ya.');
        }

        session([self::SESSION_LAST_SENT => $now]);

        $member = Auth::user()?->member ?? null;

        $result = $this->chatbotService->processMessage($message, $member);

        $history = session(self::SESSION_HISTORY, []);
        $history[] = [
            'user' => $message,
            'bot' => $result['reply'] ?? 'Maaf, terjadi kesalahan saat memproses pesanmu.',
            'intent' => $result['intent'] ?? 'fallback',
        ];

        session([self::SESSION_HISTORY => array_slice($history, -self::HISTORY_LIMIT)]);

        return redirect()->route('chatbot.index');
    }

    public function clearHistory(): RedirectResponse
    {
        $this->ensureSiswa();

        session()->forget(self::SESSION_HISTORY);

        return redirect()
            ->route('chatbot.index')
            ->with('success', 'Riwayat chatbot berhasil dihapus.');
    }

    /**
     * Chatbot hanya untuk siswa. Administrator ditolak di sisi server.
     */
    private function ensureSiswa(): void
    {
        abort_unless(Auth::user()?->role === 'siswa', 403);
    }
}