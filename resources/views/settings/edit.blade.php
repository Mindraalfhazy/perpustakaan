@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card content-card">
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="fw-bold mb-1">Pengaturan Denda Perpustakaan</h5>
                        <p class="text-muted mb-0">
                            Nilai di halaman ini dipakai bersama oleh pengembalian admin, pengembalian siswa, chatbot, dan notifikasi sistem.
                        </p>
                    </div>

                    <form action="{{ route('admin.settings.update') }}" method="POST" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-12">
                            <label for="late_fee_per_day" class="form-label fw-semibold">Denda keterlambatan per hari</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input
                                    type="number"
                                    min="0"
                                    step="1"
                                    id="late_fee_per_day"
                                    name="late_fee_per_day"
                                    class="form-control @error('late_fee_per_day') is-invalid @enderror"
                                    value="{{ old('late_fee_per_day', $lateFeePerDay) }}"
                                    required
                                >
                            </div>
                            @error('late_fee_per_day')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Contoh: isi `2000` untuk denda Rp2.000 per hari.</div>
                        </div>

                        <div class="col-12">
                            <label for="book_replacement_price" class="form-label fw-semibold">Denda kehilangan per buku</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input
                                    type="number"
                                    min="0"
                                    step="1"
                                    id="book_replacement_price"
                                    name="book_replacement_price"
                                    class="form-control @error('book_replacement_price') is-invalid @enderror"
                                    value="{{ old('book_replacement_price', $bookReplacementPrice) }}"
                                    required
                                >
                            </div>
                            @error('book_replacement_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn app-btn-primary">
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card content-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Ringkasan Aktif</h6>
                    <p class="mb-2">
                        <strong>Denda keterlambatan:</strong>
                        Rp{{ number_format($lateFeePerDay, 0, ',', '.') }}/hari
                    </p>
                    <p class="mb-0">
                        <strong>Denda kehilangan:</strong>
                        Rp{{ number_format($bookReplacementPrice, 0, ',', '.') }}/buku
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
