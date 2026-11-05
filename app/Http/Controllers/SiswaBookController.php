<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiswaBookController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $kategori = $request->string('kategori')->toString();

        $books = Book::query()
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

        return view('siswa.books', [
            'title' => 'Katalog Buku',
            'breadcrumb' => ['Katalog Buku'],
            'books' => $books,
            'search' => $search,
            'kategori' => $kategori ?: 'Semua',
            'kategoriOptions' => ['Semua', 'Pelajaran', 'Fiksi', 'Non-Fiksi', 'Referensi', 'Lainnya'],
        ]);
    }
}
