@extends('layouts.app')

@section('title', $title)

@push('styles')
    <style>
        .student-stat-card {
            position: relative;
            overflow: hidden;
            border: 0;
            box-shadow: 0 24px 48px -30px rgba(15, 23, 42, 0.35);
            background: linear-gradient(145deg, rgba(255,255,255,0.98), rgba(248,250,252,0.95));
        }
        .student-stat-card::after {
            content: "";
            position: absolute;
            top: -34px;
            right: -34px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--card-glow), transparent 70%);
        }
        .student-stat-card .card-body { position: relative; z-index: 1; }
        .student-stat-card.primary { --card-glow: rgba(79, 70, 229, 0.30); }
        .student-stat-card.success { --card-glow: rgba(5, 150, 105, 0.26); }
        .student-stat-card.warning { --card-glow: rgba(217, 119, 6, 0.24); }
    </style>
@endpush

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card content-card student-stat-card primary h-100" style="border-left: 4px solid var(--app-primary);">
                <div class="card-body">
                    <div class="text-muted small mb-2 fw-semibold">Nama Siswa</div>
                    <div class="fs-5 fw-bold text-dark">{{ $member?->nama ?? auth()->user()->name }}</div>
                    <div class="text-muted mt-1">{{ $member?->kelas ?? auth()->user()->kelas ?? 'Belum diatur' }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card content-card student-stat-card success h-100" style="border-left: 4px solid #059669;">
                <div class="card-body">
                    <div class="text-muted small mb-2 fw-semibold">NISN</div>
                    <div class="fs-5 fw-bold text-dark">{{ $member?->nisn ?? auth()->user()->nisn ?? '-' }}</div>
                    <div class="text-muted mt-1">Data anggota perpustakaan</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card content-card student-stat-card warning h-100" style="border-left: 4px solid #D97706;">
                <div class="card-body">
                    <div class="text-muted small mb-2 fw-semibold">Pinjaman Aktif</div>
                    <div class="fs-3 fw-bold text-dark">{{ $activeLoans->count() }}</div>
                    <div class="text-muted mt-1">Sedang dipinjam atau terlambat</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <a href="{{ route('siswa.card.download') }}" class="btn btn-primary">
            Cetak Kartu Anggota
        </a>
    </div>

    <div class="card content-card">
        <div class="card-header bg-white fw-bold py-3">Pinjaman Aktif Saya</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activeLoans as $loan)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $loan->book?->judul ?? '-' }}</td>
                                <td>{{ $loan->tgl_pinjam?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $loan->tgl_kembali?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $loan->status === 'terlambat' ? 'badge-soft-red' : 'badge-soft-yellow' }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada pinjaman aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection