@extends('layouts.app')

@section('title', $title)

@push('styles')
    <style>
        .report-stat-card {
            position: relative;
            overflow: hidden;
            border: 0;
            background: linear-gradient(145deg, rgba(255,255,255,0.98), rgba(248,250,252,0.94));
            box-shadow: 0 24px 48px -30px rgba(15, 23, 42, 0.35);
        }
        .report-stat-card::after {
            content: "";
            position: absolute;
            right: -30px;
            top: -30px;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--stat-color-soft), transparent 70%);
        }
        .report-stat-card .card-body { position: relative; z-index: 1; }
        .report-stat-card.primary { --stat-color: #4F46E5; --stat-color-soft: rgba(79, 70, 229, 0.28); }
        .report-stat-card.success { --stat-color: #059669; --stat-color-soft: rgba(5, 150, 105, 0.24); }
        .report-stat-card.warning { --stat-color: #D97706; --stat-color-soft: rgba(217, 119, 6, 0.24); }
        .report-stat-card.danger { --stat-color: #DC2626; --stat-color-soft: rgba(220, 38, 38, 0.22); }
        .report-stat-card .stat-label { color: var(--stat-color); font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; }
        .report-stat-card .stat-value { color: #0F172A; text-shadow: 0 12px 24px rgba(255,255,255,0.35); }
    </style>
@endpush

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card content-card report-stat-card primary h-100">
                <div class="card-body">
                    <div class="small mb-2 stat-label">Total Data</div>
                    <div class="fs-3 fw-bold stat-value">{{ $totalPeminjaman }}</div>
                    <div class="text-muted">Sesuai filter laporan</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card content-card report-stat-card success h-100">
                <div class="card-body">
                    <div class="small mb-2 stat-label">Pengembalian</div>
                    <div class="fs-3 fw-bold stat-value">{{ $totalPengembalian }}</div>
                    <div class="text-muted">Sudah dikembalikan</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card content-card report-stat-card warning h-100">
                <div class="card-body">
                    <div class="small mb-2 stat-label">Terlambat</div>
                    <div class="fs-3 fw-bold stat-value">{{ $bukuTerlambat }}</div>
                    <div class="text-muted">Butuh tindak lanjut</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card content-card report-stat-card danger h-100">
                <div class="card-body">
                    <div class="small mb-2 stat-label">Total Denda</div>
                    <div class="fs-5 fw-bold stat-value">Rp {{ number_format($totalDenda, 0, ',', '.') }}</div>
                    <div class="text-muted">Akumulasi periode ini</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card content-card">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label for="jenis" class="form-label">Jenis Laporan</label>
                    <select id="jenis" name="jenis" class="form-select">
                        @foreach (['Peminjaman', 'Pengembalian', 'Denda'] as $option)
                            <option value="{{ $option }}" @selected($jenis === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <select id="bulan" name="bulan" class="form-select">
                        @for ($month = 1; $month <= 12; $month++)
                            <option value="{{ $month }}" @selected($bulan === $month)>
                                {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <input type="number" id="tahun" name="tahun" class="form-control" min="2020" max="2100" value="{{ $tahun }}">
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-outline-secondary">Tampilkan</button>
                </div>
                <div class="col-md-2 d-grid">
    <a href="{{ route('admin.reports.exportPdf', request()->query()) }}" target="_blank" class="btn btn-outline-danger">
        <i data-feather="printer"></i> Cetak PDF
    </a>
</div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Judul Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Tgl Dikembalikan</th>
                            <th>Status</th>
                            <th>Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loans as $loan)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $loan->member?->nama ?? '-' }}</td>
                                <td>{{ $loan->book?->judul ?? '-' }}</td>
                                <td>{{ $loan->tgl_pinjam?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $loan->tgl_kembali?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $loan->tgl_dikembalikan?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $loan->status === 'dikembalikan' ? 'badge-soft-green' : ($loan->status === 'terlambat' ? 'badge-soft-red' : 'badge-soft-yellow') }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </td>
                                <td>Rp {{ number_format($loan->denda ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada data laporan untuk filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
