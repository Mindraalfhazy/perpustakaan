<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookStoreRequest;
use App\Http\Requests\BookUpdateRequest;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $books = Book::query()
            ->select('books.*')
            ->selectSub(function ($query) {
                $query->from('borrow_books')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('borrow_books.isbn', 'books.isbn')
                    ->whereNull('borrow_books.return_at');
            }, 'sedang_dipinjam')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%");
                });
            })
            ->orderBy('judul')
            ->paginate(10)
            ->withQueryString();

        return view('books.index', [
            'title' => 'Data Buku',
            'breadcrumb' => ['Dashboard', 'Data Buku'],
            'books' => $books,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('books.create', [
            'title' => 'Tambah Buku Baru',
            'breadcrumb' => ['Dashboard', 'Data Buku', 'Tambah Buku'],
            'book' => new Book(),
            'categories' => $this->categories(),
        ]);
    }

    public function store(BookStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['category_id'] = $this->resolveCategoryId($data['kategori']);
        $data['tersedia'] = $data['stok'];
        unset($data['cover'], $data['ebook']);

        $book = Book::create($data);

        if ($request->hasFile('cover')) {
            $this->storeCoverFile($book, $request->file('cover'));
        }

        if ($request->hasFile('ebook')) {
            $this->storeEbookFile($book, $request->file('ebook'));
        }

        return redirect()
            ->route('admin.books.index')
            ->with('success', 'Buku berhasil ditambahkan.');
    }

    public function show(Book $book): RedirectResponse
    {
        return redirect()->route('admin.books.edit', $book);
    }

    public function edit(Book $book): View
    {
        return view('books.edit', [
            'title' => 'Edit Data Buku',
            'breadcrumb' => ['Dashboard', 'Data Buku', 'Edit Buku'],
            'book' => $book,
            'categories' => $this->categories(),
        ]);
    }

    public function update(BookUpdateRequest $request, Book $book): RedirectResponse
    {
        $data = $request->validated();
        $data['category_id'] = $this->resolveCategoryId($data['kategori']);
        $stokLama = $book->stok;
        $stokBaru = $data['stok'];
        $selisih = $stokBaru - $stokLama;
        $data['tersedia'] = max(0, $book->tersedia + $selisih);
        unset($data['cover']);

        $book->update($data);

        if ($request->hasFile('cover')) {
            $this->storeCoverFile($book, $request->file('cover'));
        }

        return redirect()
            ->route('admin.books.index')
            ->with('success', 'Buku berhasil diperbarui.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->deleteCoverFile($book);
        $this->deleteEbookFile($book);
        $book->delete();

        return redirect()
            ->route('admin.books.index')
            ->with('success', 'Buku berhasil dihapus.');
    }

    public function uploadEbook(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'ebook' => ['required', 'file', 'mimes:pdf', 'max:51200'],
        ], [], [
            'ebook' => 'file ebook',
        ]);

        $this->storeEbookFile($book, $validated['ebook']);

        return redirect()
            ->route('admin.books.edit', $book)
            ->with('success', 'File ebook berhasil diunggah.');
    }

    public function destroyEbook(Book $book): RedirectResponse
    {
        $this->deleteEbookFile($book);

        return redirect()
            ->route('admin.books.edit', $book)
            ->with('success', 'File ebook berhasil dihapus.');
    }

    private function categories(): array
    {
        return ['Pelajaran', 'Fiksi', 'Non-Fiksi', 'Referensi', 'Lainnya'];
    }

    private function resolveCategoryId(string $categoryName): int
    {
        return Category::query()->firstOrCreate(
            ['category_name' => $categoryName],
            ['category_name' => $categoryName]
        )->id;
    }

    private function storeEbookFile(Book $book, UploadedFile $file): void
    {
        $this->deleteEbookFile($book);

        $path = $file->store('ebook', 'public');

        $book->forceFill([
            'ebook_path' => $path,
            'has_ebook' => true,
        ])->save();
    }

    private function storeCoverFile(Book $book, UploadedFile $file): void
    {
        $this->deleteCoverFile($book);

        $path = $file->store('book-covers', 'public');

        $book->forceFill([
            'cover_path' => $path,
        ])->save();
    }

    private function deleteEbookFile(Book $book): void
    {
        if ($book->ebook_path) {
            Storage::disk('public')->delete($book->ebook_path);
        }

        $book->forceFill([
            'ebook_path' => null,
            'has_ebook' => false,
        ])->save();
    }

    private function deleteCoverFile(Book $book): void
    {
        if (! $book->cover_path) {
            return;
        }

        Storage::disk('public')->delete($book->cover_path);

        $book->forceFill([
            'cover_path' => null,
        ])->save();
    }
}
