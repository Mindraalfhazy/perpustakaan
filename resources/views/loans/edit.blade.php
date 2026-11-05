@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="card content-card">
        <div class="card-body">
            <form action="{{ route('admin.loans.update', $loan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Anggota*</label>
                        <select name="member_id" class="form-select select2" required>
                            <option value="">Pilih anggota</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" @selected(old('member_id', $loan->member_id) == $member->id)>[{{ $member->nisn }}] - {{ $member->nama }} ({{ $member->kelas }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Buku*</label>
                        <select name="book_id" class="form-select select2" required>
                            <option value="">Pilih buku</option>
                            @foreach ($books as $book)
                                <option value="{{ $book->id }}" @disabled($book->tersedia == 0 && $book->id !== $loan->book_id) @selected(old('book_id', $loan->book_id) == $book->id)>
                                    {{ $book->judul }} (Tersedia: {{ $book->id === $loan->book_id ? $book->tersedia + 1 : $book->tersedia }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tgl Pinjam*</label>
                        <input type="date" id="tgl_pinjam" name="tgl_pinjam" class="form-control" value="{{ old('tgl_pinjam', $loan->tgl_pinjam->toDateString()) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tgl Kembali*</label>
                        <input type="date" id="tgl_kembali" name="tgl_kembali" class="form-control" value="{{ old('tgl_kembali', $loan->tgl_kembali->toDateString()) }}" required>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn app-btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('.select2').select2();

        $('#tgl_pinjam').on('change', function () {
            const startDate = new Date(this.value);
            if (!Number.isNaN(startDate.getTime())) {
                startDate.setDate(startDate.getDate() + 7);
                $('#tgl_kembali').val(startDate.toISOString().slice(0, 10));
            }
        });
    </script>
@endpush
