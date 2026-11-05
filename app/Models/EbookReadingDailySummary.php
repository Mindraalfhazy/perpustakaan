<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EbookReadingDailySummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'summary_date',
        'user_id',
        'book_id',
        'kelas',
        'book_category',
        'total_sessions',
        'total_duration_seconds',
        'last_read_at',
    ];

    protected $casts = [
        'summary_date' => 'date',
        'total_sessions' => 'integer',
        'total_duration_seconds' => 'integer',
        'last_read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
