@extends('layouts.app')

@section('title', $title)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <style>
        /* biar tinggi select2 sama dengan form-control bootstrap */
        .select2-container--default .select2-selection--single {
            height: calc(2.375rem + 2px);
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5;
            padding-left: 0;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.375rem);
        }
    </style>
@endpush

@section('content')
    <div class="card content-card">
        <div class="card-header bg-white fw-semibold">Form Pinjam Buku</div>
        <div class="card-body">

            <form action="{{ route('siswa.loans.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Judul Buku</label>
                    <select name="book_id" id="book_id" class="form-select" required style="width: 100%;">
                        <option value="">-- Pilih Buku --</option>
                        @foreach ($books as $book)
                            <option value="{{ $book->id }}" data-tersedia="{{ $book->tersedia }}"
                                {{ old('book_id') == $book->id ? 'selected' : '' }}>
                                {{ $book->judul }} (Tersedia: {{ $book->tersedia }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jumlah Buku Dipinjam</label>
                    <input type="number" name="jumlah" id="jumlah" class="form-control"
                           value="{{ old('jumlah', 1) }}" min="1" required>
                    <small id="tersediaInfo" class="text-muted">Pilih buku dulu untuk lihat stok.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Pinjam</label>
                    <input type="date" name="tgl_pinjam" class="form-control"
                           value="{{ old('tgl_pinjam', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                    <small class="text-muted">Tanggal kembali otomatis 7 hari dari tanggal pinjam.</small>
                </div>

                <button type="submit" class="btn btn-primary">Ajukan Peminjaman</button>
                <a href="{{ route('siswa.loans.index') }}" class="btn btn-secondary">Batal</a>
            </form>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#book_id').select2({
                placeholder: '-- Cari Buku --',
                allowClear: true,
                width: '100%'
            });
        });

        document.getElementById('book_id').addEventListener('change', function () {
            const tersedia = this.options[this.selectedIndex].getAttribute('data-tersedia') || 0;
            document.getElementById('tersediaInfo').textContent = 'Stok tersedia: ' + tersedia;
            document.getElementById('jumlah').setAttribute('max', tersedia);
        });
    </script>
    @endpush
@endsection