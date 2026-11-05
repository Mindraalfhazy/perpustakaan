<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Dipanggil via AJAX polling dari bell icon di navbar.
     */
    public function index(): JsonResponse
    {
        // Sesuaikan Auth::user() di sini kalau notifikasi dikirim ke Member, bukan User.
        $notifiable = Auth::user();

        if (! $notifiable) {
            return response()->json(['unread_count' => 0, 'notifications' => []]);
        }

        return response()->json([
            'unread_count' => $notifiable->unreadNotifications()->count(),
            'notifications' => $notifiable->notifications()
                ->latest()
                ->take(10)
                ->get()
                ->map(fn ($n) => [
                    'id' => $n->id,
                    'message' => $n->data['message'] ?? '',
                    'read' => $n->read_at !== null,
                    'created_at' => $n->created_at->diffForHumans(),
                ]),
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = Auth::user()?->notifications()->where('id', $id)->first();

        $notification?->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(): JsonResponse
    {
        Auth::user()?->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
