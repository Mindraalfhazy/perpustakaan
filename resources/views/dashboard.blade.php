@extends('layouts.app')

@section('title', $title)

@push('styles')
    <style>
        .dashboard-stat-card {
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 20px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.98), rgba(245, 247, 255, 0.95));
            box-shadow: 0 24px 45px -28px rgba(15, 23, 42, 0.35);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dashboard-stat-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--card-tint), rgba(255, 255, 255, 0.08));
            opacity: 0.9;
        }
        .dashboard-stat-card::after {
            content: "";
            position: absolute;
            right: -34px;
            top: -34px;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
        }
        .dashboard-stat-card:hover {
            transform: translateY(-6px) rotateX(4deg);
            box-shadow: 0 30px 60px -26px rgba(15, 23, 42, 0.42);
        }
        .dashboard-stat-card .card-body { position: relative; z-index: 1; }
        .dashboard-icon {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.55), 0 16px 28px -18px rgba(15, 23, 42, 0.7);
        }
        .dashboard-icon i { width: 22px; height: 22px; stroke-width: 2px; }
        .dashboard-stat-card.variant-indigo { --card-tint: rgba(79, 70, 229, 0.20); }
        .dashboard-stat-card.variant-emerald { --card-tint: rgba(16, 185, 129, 0.20); }
        .dashboard-stat-card.variant-amber { --card-tint: rgba(245, 158, 11, 0.22); }
        .dashboard-stat-card.variant-cyan { --card-tint: rgba(6, 182, 212, 0.22); }
        .dashboard-stat-card.variant-blue { --card-tint: rgba(37, 99, 235, 0.20); }
        .dashboard-stat-card.variant-rose { --card-tint: rgba(244, 63, 94, 0.20); }
        .chart-wrap { min-height: 300px; position: relative; }
        .fs-3 { font-size: 1.5rem !important; letter-spacing: -0.02em; }
    </style>
@endpush

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card dashboard-stat-card variant-indigo h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold mb-1">Total Judul</div>
                        <div class="fs-3 fw-bold" style="color: #0F172A;">{{ $totalBooks }}</div>
                    </div>
                    <div class="dashboard-icon" style="background:#EEF2FF;color:#4F46E5;"><i data-feather="book"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card dashboard-stat-card variant-emerald h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold mb-1">Anggota</div>
                        <div class="fs-3 fw-bold" style="color: #0F172A;">{{ $totalMembers }}</div>
                    </div>
                    <div class="dashboard-icon" style="background:#ECFDF5;color:#059669;"><i data-feather="users"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card dashboard-stat-card variant-amber h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold mb-1">Dipinjam</div>
                        <div class="fs-3 fw-bold" style="color: #0F172A;">{{ $activeLoans }}</div>
                    </div>
                    <div class="dashboard-icon" style="background:#FFFBEB;color:#D97706;"><i data-feather="book-open"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card dashboard-stat-card variant-cyan h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold mb-1">Sesi E-Book</div>
                        <div class="fs-3 fw-bold" style="color: #0F172A;">{{ $ebookSessions }}</div>
                    </div>
                    <div class="dashboard-icon" style="background:#F5F3FF;color:#6366F1;"><i data-feather="monitor"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card dashboard-stat-card variant-blue h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold mb-1">Pembaca Aktif</div>
                        <div class="fs-3 fw-bold" style="color: #0F172A;">{{ $activeReaders }}</div>
                    </div>
                    <div class="dashboard-icon" style="background:#EFF6FF;color:#2563EB;"><i data-feather="activity"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card dashboard-stat-card variant-rose h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold mb-1">Total Denda</div>
                        <div class="fs-6 fw-bold" style="color: #0F172A;">Rp {{ number_format($totalFines, 0, ',', '.') }}</div>
                    </div>
                    <div class="dashboard-icon" style="background:#FEF2F2;color:#DC2626;"><i data-feather="alert-circle"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card content-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="fw-semibold">Peminjaman Terbaru</div>
                    <div class="text-muted small">Aktivitas peminjaman terbaru dari anggota perpustakaan.</div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Judul Buku</th>
                                    <th>Tgl Pinjam</th>
                                    <th>Tgl Kembali</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestLoans as $loan)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $loan->member->nama }}</td>
                                        <td>{{ $loan->book->judul }}</td>
                                        <td>{{ $loan->tgl_pinjam->format('d/m/Y') }}</td>
                                        <td>{{ $loan->tgl_kembali->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge {{ $loan->status === 'dipinjam' ? 'badge-soft-yellow' : ($loan->status === 'dikembalikan' ? 'badge-soft-green' : 'badge-soft-red') }}">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada data peminjaman.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card content-card mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="fw-semibold">Buku Paling Sering Dipinjam</div>
                    <div class="text-muted small">Top judul berdasarkan frekuensi peminjaman.</div>
                </div>
                <div class="card-body pt-0">
                    @forelse ($popularBooks as $book)
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div>{{ $loop->iteration }}. {{ $book->book?->judul ?? '-' }}</div>
                            <span class="badge badge-soft-purple">{{ $book->total_pinjam }}</span>
                        </div>
                    @empty
                        <div class="text-muted">Belum ada data.</div>
                    @endforelse
                </div>
            </div>

            <div class="card content-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="fw-semibold">Tren Baca E-Book 7 Hari</div>
                    <div class="text-muted small">Ringkas aktivitas baca untuk memantau keterlibatan siswa.</div>
                </div>
                <div class="card-body pt-0">
                    <div class="chart-wrap">
                        <canvas id="ebook-trend-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('ebook-trend-chart'), {
            type: 'line',
            data: {
                labels: @json($ebookTrend->map(fn ($item) => \Carbon\Carbon::parse($item->activity_date)->translatedFormat('d M'))->values()),
                datasets: [{
                    data: @json($ebookTrend->pluck('total_sessions')->map(fn ($value) => (int) $value)->values()),
                    borderColor: '#4F46E5',
                    backgroundColor: 'rgba(79, 70, 229, 0.12)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#4F46E5',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: 'rgba(156, 163, 175, 0.15)' }, ticks: { precision: 0 } },
                },
            }
        });
    </script>
@endpush
