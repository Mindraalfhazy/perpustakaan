<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'judul',
        'penulis',
        'tempat_terbit',
        'penerbit',
        'tahun',
        'kategori',
        'stok',
        'tersedia',
        'cover_path',
        'ebook_path',
        'has_ebook',
    ];

    protected $casts = [
        'has_ebook' => 'bool',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function readingLogs(): HasMany
    {
        return $this->hasMany(EbookReadingLog::class);
    }
}
