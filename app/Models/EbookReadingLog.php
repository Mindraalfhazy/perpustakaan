<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EbookReadingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'opened_at',
        'last_read_at',
        'duration_seconds',
        'device_type',
        'last_page_read',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'last_read_at' => 'datetime',
        'duration_seconds' => 'integer',
        'last_page_read' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function getDurationLabelAttribute(): string
    {
        $totalSeconds = max(0, (int) $this->duration_seconds);

        if ($totalSeconds < 60) {
            return $totalSeconds . ' detik';
        }

        $hours = intdiv($totalSeconds, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);
        $seconds = $totalSeconds % 60;

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours . ' jam';
        }

        if ($minutes > 0) {
            $parts[] = $minutes . ' menit';
        }

        if ($hours === 0 && $seconds > 0) {
            $parts[] = $seconds . ' detik';
        }

        return implode(' ', $parts);
    }
}
