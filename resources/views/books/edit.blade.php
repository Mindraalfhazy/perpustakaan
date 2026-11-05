@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="card content-card">
        <div class="card-body">
            <form action="{{ route('admin.books.update', $book) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('books._form')
            </form>
        </div>
    </div>

    <div class="card content-card mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">Kelola E-Book</h2>
                    <div class="text-muted small">
                        {{ $book->has_ebook ? 'File ebook sudah tersedia.' : 'Belum ada file ebook untuk buku ini.' }}
                    </div>
                </div>
                @if ($book->has_ebook && $book->ebook_path)
                    <span class="badge badge-soft-green">Ada Ebook</span>
                @endif
            </div>

            @if ($book->ebook_path)
                <div class="alert alert-light border mb-3">
                    <div class="fw-semibold">File saat ini</div>
                    <div class="text-muted">{{ basename($book->ebook_path) }}</div>
                </div>
            @endif

            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <form action="{{ route('admin.books.upload-ebook', $book) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label">Upload / Ganti File PDF</label>
                            <input type="file" name="ebook" class="form-control" accept="application/pdf" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary" style="background:#6D28D9;border-color:#6D28D9;">
                                Simpan Ebook
                            </button>
                        </div>
                    </form>
                </div>

                @if ($book->has_ebook)
                    <div class="col-md-4">
                        <form action="{{ route('admin.books.destroy-ebook', $book) }}" method="POST" onsubmit="return confirm('Yakin hapus file ebook ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">Hapus Ebook</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
