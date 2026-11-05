@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="card content-card">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <form method="GET" class="d-flex gap-2 flex-wrap">
                <input type="text" name="search" class="form-control"
                    placeholder="Cari judul atau penulis..."
                    value="{{ $search }}">
                <button class="btn btn-outline-secondary">Cari</button>
            </form>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.ebooks.history') }}"
                    class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i data-feather="clock" style="width: 16px; height: 16px;"></i> History E-Book
                </a>
                <a href="{{ route('admin.books.create') }}"
                    class="btn app-btn-primary d-flex align-items-center gap-2">
                    <i data-feather="plus" style="width: 16px; height: 16px;"></i> Tambah Buku
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Sampul</th>
                        <th>Penulis</th>
                        <th>Tempat Terbit</th>
                        <th>Penerbit</th>
                        <th>Tahun Terbit</th>
                        <th>Stok</th>
                        <th>Tersedia</th>
                        <th>E-Book</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($books as $book)

                    <tr>

                        <td>{{ $books->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="fw-semibold">{{ $book->judul }}</div>
                            @if ($book->has_ebook)
                                <span class="badge badge-soft-purple mt-1">Ada Ebook</span>
                            @endif
                        </td>
                        <td>
                            @if ($book->cover_path)
                                <img src="{{ asset('storage/' . $book->cover_path) }}"
                                     alt="Sampul {{ $book->judul }}"
                                     style="width:52px;height:72px;object-fit:cover;border-radius:10px;border:1px solid #e2e8f0;">
                            @else
                                <span class="badge badge-soft-gray">Belum Ada</span>
                            @endif
                        </td>
                        <td>{{ $book->penulis }}</td>
                        <td>{{ $book->tempat_terbit ?? '-' }}</td>
                        <td>{{ $book->penerbit ?? '-' }}</td>
                        <td>{{ $book->tahun }}</td>

                        <td>{{ $book->stok }}</td>

                        <td>
                            @if($book->tersedia > 0)
                                <span class="badge badge-soft-green">
                                    {{ $book->tersedia }} tersedia
                                </span>
                            @else
                                <span class="badge badge-soft-red">
                                    Habis
                                </span>
                            @endif
                        </td>

                        <td>
                            @if ($book->has_ebook)
                                <span class="badge badge-soft-green">Tersedia</span>
                            @else
                                <span class="badge badge-soft-gray">Belum Ada</span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex gap-2">

                                <a href="{{ route('admin.books.edit',$book) }}"
                                    class="btn btn-sm btn-outline-primary" style="padding: 6px 10px; border-radius: 8px;">
                                    <i data-feather="edit" style="width: 16px; height: 16px;"></i>
                                </a>

                                <form action="{{ route('admin.books.destroy',$book) }}"
                                    method="POST"
                                    onsubmit="return confirm('Yakin hapus buku ini?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-outline-danger" style="padding: 6px 10px; border-radius: 8px;">
                                        <i data-feather="trash" style="width: 16px; height: 16px;"></i>
                                    </button>

                                </form>

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="11" class="text-center">
                            Belum ada data buku.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $books->links('pagination::bootstrap-5') }}
        </div>

    </div>
</div>
@endsection
